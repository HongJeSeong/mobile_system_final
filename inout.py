import spidev
import RPi.GPIO as GPIO
import time
from datetime import datetime
import Queue
import numpy
import pymysql
import urllib
import json

GPIO.setmode(GPIO.BOARD)

led_pre = 11
led_abs = 13
led_work = 15
led_wait = 12

GPIO.setup(led_pre,GPIO.OUT,initial=GPIO.LOW)
GPIO.setup(led_abs,GPIO.OUT,initial=GPIO.HIGH)
GPIO.setup(led_work,GPIO.OUT,initial=GPIO.LOW)
GPIO.setup(led_wait,GPIO.OUT,initial=GPIO.LOW)

sgl = 0x20
ch0 = 0x00
ch1 = 0x10
spi = spidev.SpiDev()
spi.open(0, 0) 
msbf = 0x08
spi.max_speed_hz=1000000
spi.bits_per_word=8
dummy = 0xff
start = 0x47

count=0

number="420" #temporary room name
flag0=False
flag1=False

first=""
before="red"

q0 = Queue.Queue(4)
q1 = Queue.Queue(4)
###connect mysql
conn = pymysql.connect(host='192.168.1.30', user='root', password='hong', db='office_state', charset='utf8',unix_socket='/var/run/mysqld/mysqld.sock' )

curs = conn.cursor(pymysql.cursors.DictCursor)
sql = """insert into state(time,number,count)
         values (%s, %s, %s)"""

################

###connect json
url='http://192.168.1.30/data.json'
###############


def measure(ch):
	ad = spi.xfer2( [ (start + sgl + ch + msbf), dummy ] )
	val = ( ( ( (ad[0] & 0x03) << 8) + ad[1] ) * 3.3 ) / 1023
	return float(val*100)
def getAvg(que):
	return numpy.mean(list(que.queue))	

def inOut():
	now=datetime.now()
	global count
	if flag0==True:
		if flag1==True and first=="0":
			count+=1
			reset()
			curs.execute(sql,(now,number,count))
			conn.commit()
			return count
	if flag1==True:
		if flag0==True and first=="1":
			if count > 0:
				count-=1
			reset()
			curs.execute(sql,(now,number,count))
                        conn.commit()
			return count

def led_room(count):
	if count > 0:
		GPIO.output(led_pre,GPIO.HIGH)
		GPIO.output(led_abs,GPIO.LOW)
	else :
		GPIO.output(led_pre,GPIO.LOW)
		GPIO.output(led_abs,GPIO.HIGH)
		
def led_state(msg,temp):
	global count
	if msg == temp:
		return temp
	if msg=="red":
		GPIO.output(led_abs,GPIO.HIGH)
		GPIO.output(led_pre,GPIO.LOW)
		GPIO.output(led_work,GPIO.LOW)
		GPIO.output(led_wait,GPIO.LOW)
		count=0	
	elif msg=="yellow":
		GPIO.output(led_work,GPIO.HIGH)
		GPIO.output(led_wait,GPIO.LOW)
	elif msg == "blue":
		GPIO.output(led_work,GPIO.LOW)
		GPIO.output(led_wait,GPIO.HIGH)
	temp=msg
	return temp
	

def reset():
	global flag0,flag1,first
	flag0=False
	flag1=False	
	first=""
		

try:
	while 1:
		mes_ch0 = measure(ch0)
		mes_ch1 = measure(ch1)
		if q0.full() == True:
			q0.get()
		if q1.full() == True:
			q1.get()

		q0.put(mes_ch0)
		q1.put(mes_ch1)
		q0_mean = getAvg(q0)
		q1_mean = getAvg(q1)
		if mes_ch0 < q0_mean*0.9:
			print("sensor 0 DOWN")
			flag0=True
			if first=="":
				first="0"
		if mes_ch1 < q1_mean*0.9:
			print("sensor 1 DOWN")
			flag1=True
			if first=="":
				first="1"
		inOut()
		print("person",count)
		time.sleep(0.3)
		##################read json data
		u=urllib.urlopen(url)
		data = u.read()
		val = json.loads(data)
		##################control led
		led_room(count)
		before=led_state(val["color"],before)
except KeyboardInterrupt:
	pass
spi.close()
GPIO.cleanup()
