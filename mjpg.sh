udo modprobe bcm2835-v4l2

export STREAMER_PATH=$HOME/mjpg-streamer/mjpg-streamer-experimental
export LD_LIBRARY_PATH=$STREAMER_PATH
$STREAMER_PATH/mjpg_streamer -i "input_uvc.so -d /dev/video0 -n" -o "output_http.so -w $STREAMER_PATH/www"
