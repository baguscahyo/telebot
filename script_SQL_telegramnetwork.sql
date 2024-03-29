CREATE TABLE `monitor_rto` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(20) NOT NULL,
  `nama_unit` varchar(30) NOT NULL,
  `tanggal_rto` date NOT NULL,
  `jam_rto` time NOT NULL,
  `tanggal_reply` date NOT NULL,
  `jam_reply` time NOT NULL,
  `durasi` int(10) NOT NULL,
  `keterangan` varchar(150) NOT NULL,
  `menit_ke` int(2) NOT NULL,
  `email` int(2) NOT NULL,
  `tanggal_rto2` datetime DEFAULT NULL,
  `tanggal_reply2` datetime DEFAULT NULL,
  `durasi2` int(11) DEFAULT NULL,
  `diabaikan` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

CREATE TABLE `data_ip` (
  `ID` int(2) NOT NULL AUTO_INCREMENT,
  `service_id` varchar(50) DEFAULT NULL,
  `ip_address` varchar(20) NOT NULL,
  `nama_unit` varchar(50) NOT NULL,
  `kategori` varchar(100) NOT NULL DEFAULT 'Network',
  `subkategori` varchar(200) DEFAULT NULL,
  `pic` varchar(10) DEFAULT NULL,
  `gps_x` varchar(50) DEFAULT NULL,
  `gps_y` varchar(50) DEFAULT NULL,
  `sla` decimal(10,2) DEFAULT '1.00',
  `dipindai` int(2) DEFAULT '1',
  `status` varchar(20) NOT NULL DEFAULT 'offline',
  `jam_rto` time NOT NULL,
  `tanggal_rto` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggal_reply` datetime DEFAULT NULL,
  `tanggal_email` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=202 DEFAULT CHARSET=latin1;
