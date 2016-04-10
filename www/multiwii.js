/* This implements MultiWii websocket proxy protocol */

function MultiWii() {
	MAX_MSG_LEN = 32;
	endiness = true; //defines the endiness of the mw proxy (should not be changed unless the mw proxy implementation changes)
	client_isLittleEndian = 0; //little-endian by default

	function isLittleEndian() {
		var a1 = new Uint32Array([1]);
		var a2 = new Uint8Array(a1.buffer);
		if (a2[0]==1) return true;//little endian
		else return false//big endian 
	}	

	client_isLittleEndian = isLittleEndian();

	if (!client_isLittleEndian) {
		alert("You are running on a big-endian CPU. This is currently not supported!");
	}
}


/* LIST OF ALL SERIALIZERS & PARSERS */
/* http://www.multiwii.com/wiki/index.php?title=Multiwii_Serial_Protocol */

/* Note that this is not the actual MSP protocol. 
/* The messages from JS are sent to mw server through mw proxy. Proxy and server together will convert them into actual MSP compliant format */


//for serialize - data can be written starting from 3rd byte (0,1 + data), returns number of bytes written

// ======================== CUSTOM FUNCTIONS START
MultiWii.prototype.serialize_id50 = function(dv,data) {
	//the data starts at 2nd byte (byte 0 and 1 is reserved and set automatically for id and length)
	return 0; //length of data
};

MultiWii.prototype.parse_id50 = function(dv,data,len) { 
	var ret = {
		"uart_errors_count": dv.getUint16(2,endiness),
		"uart_rx_count": dv.getUint16(4,endiness),
		"uart_tx_count": dv.getUint16(6,endiness),
		"link_rssi": -dv.getUint8(8,endiness)
	}

	return ret;
};

MultiWii.prototype.serialize_id51 = function(dv,data) {
	return 0; 
};

MultiWii.prototype.serialize_id52 = function(dv,data) {
	dv.setUint8(2,data["combo"],endiness);
	return 1; 
};

// ======================== CUSTOM FUNCTIONS END

MultiWii.prototype.serialize_id100 = function(dv,data) {
	//the data starts at 2nd byte (byte 0 and 1 is reserved and set automatically for id and length)
	return 0; //length of data
};

MultiWii.prototype.parse_id100 = function(dv,data,len) { 
	var caps = dv.getUint32(5,endiness);
	var ret = {
		//the actual data starts from 2nd byte
		'version': dv.getUint8(2,endiness),
		'multitype': dv.getUint8(3,endiness),
		'msp_version': dv.getUint8(4,endiness),
		//'capability': dv.getUint32(5,endiness),
		'capability': {
			'bind_capable': MultiWii.getBit(caps,0),
			'dynbal': MultiWii.getBit(caps,1),
			'flap': MultiWii.getBit(caps,2),
			'navcap': MultiWii.getBit(caps,3),
			'extaux': MultiWii.getBit(caps,4),
			'navi_version': 0
		}
	}

	return ret;
};

MultiWii.prototype.serialize_id101 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id101 = function(dv,data,len) { 
	var sensor = dv.getUint16(6,endiness);
	var ret = {
		'cycleTime': dv.getUint16(2,endiness),
		'i2c_errors_count': dv.getUint16(4,endiness),
		'sensor': {
			'acc': MultiWii.getBit(sensor,0),
			'baro': MultiWii.getBit(sensor,1),
			'mag': MultiWii.getBit(sensor,2),
			'gps': MultiWii.getBit(sensor,3),
			'sonar': MultiWii.getBit(sensor,4)
		},
		'flag': parseInt(dv.getUint32(8,endiness)).toString(2), //get binary format for the value
		'global_conf.currentSet': dv.getUint8(12,endiness)
	}
	return ret;
};

MultiWii.prototype.serialize_id102 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id102 = function(dv,data,len) { 
	var ret = {
		'accx': dv.getInt16(2,endiness),
		'accy': dv.getInt16(4,endiness),
		'accz': dv.getInt16(6,endiness),
		'gyrx': dv.getInt16(8,endiness),
		'gyry': dv.getInt16(10,endiness),
		'gyrz': dv.getInt16(12,endiness),		
		'magx': dv.getInt16(14,endiness),
		'magy': dv.getInt16(16,endiness),
		'magz': dv.getInt16(18,endiness)
	}
	return ret;
};

MultiWii.prototype.serialize_id104 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id104 = function(dv,data,len) { 
	var ret = {
		'motor1': dv.getUint16(2,endiness),
		'motor2': dv.getUint16(4,endiness),
		'motor3': dv.getUint16(6,endiness),
		'motor4': dv.getUint16(8,endiness),
		'motor5': dv.getUint16(10,endiness),
		'motor6': dv.getUint16(12,endiness),
		'motor7': dv.getUint16(14,endiness),
		'motor8': dv.getUint16(16,endiness),
	}
	return ret;
};

MultiWii.prototype.serialize_id105 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id105 = function(dv,data,len) { 
	var ret = {
		'roll': dv.getInt16(2,endiness),
		'pitch': dv.getInt16(4,endiness),
		'yaw': dv.getInt16(6,endiness),
		'throttle': dv.getInt16(8,endiness),
		'aux1': dv.getInt16(10,endiness),
		'aux2': dv.getInt16(12,endiness),
		'aux3': dv.getInt16(14,endiness),
		'aux4': dv.getInt16(16,endiness)
	}
	return ret;
};

MultiWii.prototype.serialize_id106 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id106 = function(dv,data,len) { 
	var ret = {
		'gps_fix': dv.getUint8(2,endiness),
		'gps_numsat': dv.getUint8(3,endiness),
		'gps_coord_lat': dv.getInt32(4,endiness)/10000000,
		'gps_coord_lon': dv.getInt32(8,endiness)/10000000,
		'gps_altitude': dv.getUint16(12,endiness),
		'gps_speed': dv.getUint16(14,endiness),
		'gps_ground_course': dv.getUint16(16,endiness)
	}
	return ret;
};

MultiWii.prototype.serialize_id107 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id107 = function(dv,data,len) { 
	var ret = {
		'GPS_distanceToHome': dv.getUint16(2,endiness),
		'GPS_directionToHome': dv.getUint16(4,endiness),
		'GPS_update': dv.getUint8(6,endiness)
	}
	return ret;
};

MultiWii.prototype.serialize_id108 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id108 = function(dv,data,len) { 
	var ret = {
		'angx': dv.getInt16(2,endiness),
		'angy': dv.getInt16(4,endiness),
		'heading': dv.getInt16(6,endiness)
	}
	return ret;
};

MultiWii.prototype.serialize_id109 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id109 = function(dv,data,len) { 
	var ret = {
		'EstAlt': dv.getInt32(2,endiness),
		'vario': dv.getInt16(6,endiness)
	}
	return ret;
};

MultiWii.prototype.serialize_id112 = function(dv) {
	return 0;
};

MultiWii.prototype.parse_id112 = function(dv,data,len) { 
	ret = {};

	for (var i=0;i<MultiWii.PID.length;i++)
		ret[ MultiWii.PID[i] ] = {
			"p": dv.getUint8(2+3*i,endiness),
			"i": dv.getUint8(2+3*i+1,endiness),
			"d": dv.getUint8(2+3*i+2,endiness)
		}

	return ret;
};

MultiWii.prototype.serialize_id113 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id113 = function(dv,data,len) { 
	var s = [];

	for (var i=2;i<len+2;i+=2) {
		s[s.length] = dv.getUint16(i,endiness);
	}

	var ret = {
		"active": s
	};
	return ret;
};

MultiWii.prototype.serialize_id114 = function(dv) {
	return 0;
};

MultiWii.prototype.parse_id114 = function(dv,data,len) { 
	var ret = {
		'intPowerTrigger1': dv.getUint16(2,endiness),
		'conf.minthrottle': dv.getUint16(4,endiness),
		'maxthrottle': dv.getUint16(6,endiness),
		'mincommand': dv.getUint16(8,endiness),
		'conf.failsafe_throttle': dv.getUint16(10,endiness),
		'plog.arm': dv.getUint16(12,endiness),
		'plog.lifetime': dv.getUint32(14,endiness),
		'conf.mag_declination': dv.getUint16(18,endiness),
		'conf.vbatscale': dv.getUint8(20,endiness),
		'conf.vbatlevel_warn1': dv.getUint8(21,endiness),
		'conf.vbatlevel_warn2': dv.getUint8(22,endiness),
		'conf.vbatlevel_crit': dv.getUint8(23,endiness)
	}
	return ret;
};

MultiWii.prototype.serialize_id116 = function(dv) {
	return 0;
};

MultiWii.prototype.parse_id116 = function(dv,data,len) { 
	var ret = {};
	var s =[];
	var name = "";

	for (var i=2;i<len+2;i++) {
		if (data[i]==59) {//;
			s[s.length] = name;
			name = "";
		} else {
			name += String.fromCharCode(data[i]);
		}
	}

	ret.boxname = s;

	return ret;
};


MultiWii.prototype.serialize_id118 = function(dv,data) {
	dv.setUint8(2,data["wp_no"],endiness);
	return 1;
};

MultiWii.prototype.parse_id118 = function(dv,data,len) { 
	var ret = {
		'wp_no': dv.getUint8(2,endiness),
		'action': dv.getUint8(3,endiness),
		'lat': dv.getInt32(4,endiness)/10000000,
		'lon': dv.getInt32(8,endiness)/10000000,
		'AltHold': dv.getUint32(12,endiness),
		'param1': dv.getInt16(16,endiness),
		'param2': dv.getInt16(18,endiness),
		'param3': dv.getInt16(20,endiness),
		'flag': dv.getUint8(22,endiness)
	}
	return ret;
}

MultiWii.prototype.serialize_id119 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id119 = function(dv,data,len) { 
 var s = [];

 for (var i=2;i<len+2;i++) {
 	s[s.length] = dv.getUint8(i,endiness);
 }

 var ret = {
 	"supported": s
 }

 return ret;
};


MultiWii.prototype.serialize_id121 = function(dv,data) {
	return 0;
};

MultiWii.prototype.parse_id121 = function(dv,data,len) { 
	var ret = {
		'gps_mode': dv.getUint8(2,endiness), //
		'nav_state': dv.getUint8(3,endiness),
		'mission_action': dv.getUint8(4,endiness),
		'mission_number': dv.getUint8(5,endiness),
		'error': dv.getUint8(6,endiness),
		'target_bearing': dv.getInt16(7,endiness)
	}
	return ret;
};

MultiWii.prototype.serialize_id200 = function(dv,data) {
	//the data starts at 2nd byte (byte 0 and 1 is reserved and set automatically for id and length)
	dv.setInt16(2,data["roll"],endiness);
	dv.setInt16(4,data["pitch"],endiness);
	dv.setInt16(6,data["yaw"],endiness);
	dv.setInt16(8,data["throttle"],endiness);
	dv.setInt16(10,data["aux1"],endiness);
	dv.setInt16(12,data["aux2"],endiness);
	dv.setInt16(14,data["aux3"],endiness);
	dv.setInt16(16,data["aux4"],endiness);
	return 16;
};


MultiWii.prototype.serialize_id202 = function(dv,data) {
	//the data starts at 2nd byte (byte 0 and 1 is reserved and set automatically for id and length)
	for (var i=0;i<MultiWii.PID.length;i++) {
		var pid = data[ MultiWii.PID[i] ];
		dv.setUint8(2+3*i,pid["p"],endiness);
		dv.setUint8(2+3*i+1,pid["i"],endiness);
		dv.setUint8(2+3*i+2,pid["d"],endiness);
	}
	return 3*i;
};

MultiWii.prototype.serialize_id203 = function(dv,data) {
	//the data starts at 2nd byte (byte 0 and 1 is reserved and set automatically for id and length)
	for (var i=0;i<data.active.length;i++) {
		dv.setUint16(2+2*i,parseInt(data.active[i]),endiness);
	}
	return 2*i;
};

MultiWii.prototype.serialize_id205 = function(dv,data) {
	return 0;
};

MultiWii.prototype.serialize_id206 = function(dv,data) {
	return 0;
};

MultiWii.prototype.serialize_id208 = function(dv,data) {
	return 0;
};

MultiWii.prototype.serialize_id214 = function(dv,data) {
	//the data starts at 2nd byte (byte 0 and 1 is reserved and set automatically for id and length)
	dv.setUint16(2,data["motor1"],endiness);
	dv.setUint16(4,data["motor2"],endiness);
	dv.setUint16(6,data["motor3"],endiness);
	dv.setUint16(8,data["motor4"],endiness);
	dv.setUint16(10,data["motor5"],endiness);
	dv.setUint16(12,data["motor6"],endiness);
	dv.setUint16(14,data["motor7"],endiness);
	dv.setUint16(16,data["motor8"],endiness);
	return 16;
};


/* END OF PARSERS AND SERIALIZERS */

MultiWii.MultiType = [
	"?",
	"TRI","QUADP",
	"QUADX",
	"BI",
	"GIMBAL",
	"Y6",
	"HEX6",
	"FLYING_WING",
	"Y4",
	"HEX6X",
	"OCTOX8",
	"OCTOFLATP",
	"OCTOFLATX",
	"AIRPLANE",
	"HELI_120",
	"HELI_90",
	"VTAIL4",
	"HEX6H",
	"SINGLECOPTER",
	"DUALCOPTER"
];

MultiWii.RC = [
  "ROLL",
  "PITCH",
  "YAW",
  "THROTTLE",
  "AUX1",
  "AUX2",
  "AUX3",
  "AUX4",
  "AUX5",
  "AUX6",
  "AUX7",
  "AUX8"
];

MultiWii.PID = [
  "PIDROLL",
  "PIDPITCH",
  "PIDYAW",
  "PIDALT",
  "PIDPOS",
  "PIDPOSR",
  "PIDNAVR",
  "PIDLEVEL",
  "PIDMAG",
  "PIDVEL"
];

MultiWii.BOX = [
  "BOXARM", //0
  "BOXANGLE", //1
  "BOXHORIZON", //2
  "BOXBARO", //3
  "BOXVARIO", //4
  "BOXMAG", //5
  "BOXHEADFREE", //6
  "BOXHEADADJ", // 7 acquire heading for HEADFREE mode
  "BOXCAMSTAB",// 8
  "BOXCAMTRIG", //9
  "BOXGPSHOME", //10
  "BOXGPSHOLD", //11
  "BOXPASSTHRU", //12
  "BOXBEEPERON", //13
  "BOXLEDMAX", //14 we want maximum illumination
  "BOXLEDLOW", //15 low/no lights
  "BOXLLIGHTS", //16 enable landing lights at any altitude
  "BOXCALIB", //17
  "BOXGOV", //18
  "BOXOSD", //19
  "BOXGPSNAV", //20
  "BOXLAND" //21
  //22
];

MultiWii.GPS_MODE = [
	"GPS_MODE_NONE",
	"GPS_MODE_HOLD",
	"GPS_MODE_RTH",
	"GPS_MODE_NAV"
];

MultiWii.NAV_STATE = [
  "NAV_STATE_NONE",
  "NAV_STATE_RTH_START",
  "NAV_STATE_RTH_ENROUTE",
  "NAV_STATE_HOLD_INFINIT",
  "NAV_STATE_HOLD_TIMED",
  "NAV_STATE_WP_ENROUTE",
  "NAV_STATE_PROCESS_NEXT",
  "NAV_STATE_DO_JUMP",
  "NAV_STATE_LAND_START",
  "NAV_STATE_LAND_IN_PROGRESS",
  "NAV_STATE_LANDED",
  "NAV_STATE_LAND_SETTLE",
  "NAV_STATE_LAND_START_DESCENT"
];

MultiWii.NAV_ERROR = [
  "NAV_ERROR_NONE",                //All systems clear
  "NAV_ERROR_TOOFAR",              //Next waypoint distance is more than safety distance
  "NAV_ERROR_SPOILED_GPS",         //GPS reception is compromised - Nav paused - copter is adrift !
  "NAV_ERROR_WP_CRC",              //CRC error reading WP data from EEPROM - Nav stopped
  "NAV_ERROR_FINISH",              //End flag detected, navigation finished
  "NAV_ERROR_TIMEWAIT",            //Waiting for poshold timer
  "NAV_ERROR_INVALID_JUMP",        //Invalid jump target detected, aborting
  "NAV_ERROR_INVALID_DATA",        //Invalid mission step action code, aborting, copter is adrift
  "NAV_ERROR_WAIT_FOR_RTH_ALT",    //Waiting to reach RTH Altitude
  "NAV_ERROR_GPS_FIX_LOST",        //Gps fix lost, aborting mission
  "NAV_ERROR_DISARMED",            //NAV engine disabled due disarm
  "NAV_ERROR_LANDING"              //Landing
];

MultiWii.STICK = [
	"STICKARM", //0
	"STICKDISARM", //1
	"STICKGYROCALIB", //2
	"STICKACCCALIB", //3
	"STICKMAGCALIB" //4
];

MultiWii.getBit = function(val,bit) {
	//TODO: handle endiness correctly - check the endiness; use bitwise operations to get the correct bit
	//Would be nice to have a browser with different endiness for testing purposes
	var v = parseInt(val).toString(2);
	if (v[bit]==undefined) return 0;
	return parseInt(v[bit]);
}

MultiWii.prototype.filters = function(data) {
	var arr = [];
	arr[0] = data.length;
	for (var i=0;i<data.length;i++)
		arr[i+1] = data[i];

	return arr;
};

MultiWii.prototype.serialize = function(data) {
	var id = data["id"];
	var ret = new Uint8Array(MAX_MSG_LEN);
	var dv = new DataView(ret.buffer);

	var _f = "serialize_id"+id;
	if (this[_f] == undefined) {
		console.log("Serializer for id: "+id+" not implemented!");
		return [];
	}
	var len = this[_f](dv,data);

	dv.setUint8(0,len);
	dv.setUint8(1,id);

	var arr = [];
	for (var i=0;i<len+2;i++)
		arr[i] = ret[i]; 
	return arr;
};


MultiWii.prototype.parse = function(data) {/*array*/
	//console.log("Parsing data",data);
	var id, data_length;
	var arr = new Uint8Array(data);
	var dv = new DataView(arr.buffer);

	if (data.length<2) {
		return {"err": "Not enough data to parse! "+data.length};
	}

	data_length = dv.getUint8(0);
	id = dv.getUint8(1);

	if (data.length<2+data_length) {
		return {"err": "Not enough data to parse! "+data.length};
	}

	var _f = "parse_id"+id;
	if (this[_f] == undefined) {
		console.log("Parser for id: "+id+" not implemented!");
		return [];
	}

	var ret = this[_f](dv,data,data_length); //dv- view on the packet (len,id,data), data- the packet array itself, data_length- data length
	ret.id = id;
	return ret;
}

MultiWii();


