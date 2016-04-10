function get_time() {
          var d = new Date(),
      h = (d.getHours()<10?'0':'') + d.getHours(),
      m = (d.getMinutes()<10?'0':'') + d.getMinutes(),
      s = (d.getSeconds()<10?'0':'') + d.getSeconds();
      return h+":"+m+":"+s;
}

function mw_recv() { //helper function to receive and parse data from websocket
    //check if we got enough data
    var len = ws.rQlen();
    var data_length;
    var data;
    if (len<2) { //not enough data to do anything, we will need to wait for more
        return {"err": "Not enough data to parse: "+len};
    }

    data_length = ws.rQpeek8();
    if (len<2+data_length) { //not enough data to do anything, we will need to wait for more
      return {"err": "Not enough data to parse: "+len};
    }

    data = ws.rQshiftBytes(2+data_length);

    return mw.parse(data);
}

function default_err() {
  console.log(arguments); 
  $("#danger").text("Websocket error! Check the mw proxy is running on "+proxy_ip+":"+proxy_port+" and it is accessible.");
  $('#danger').show();
  setTimeout(function(){$('#danger').hide();},10000); 
}