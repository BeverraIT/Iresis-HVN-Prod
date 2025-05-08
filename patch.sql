CREATE TABLE `menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parentid` int DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `uri` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sortorder` int DEFAULT NULL,
  `description` text,
  `isactive` tinyint DEFAULT NULL,
  `createdby` int DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` int DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

CREATE TABLE `roleaccess` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menuid` varchar(20) NOT NULL,
  `roleid` varchar(100) NOT NULL,
  `createdby` int DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=212 DEFAULT CHARSET=latin1;

alter table tbluser add name	varchar(100);
alter table tbluser add email	varchar(50);
alter table tbluser add lastlogin	timestamp;
alter table tbluser add isactive	tinyint;
alter table tbluser add createdby	int;
alter table tbluser add created	timestamp;
alter table tbluser add updatedby	int;
alter table tbluser add updated	timestamp;

update tbluser set isactive = 1;

INSERT INTO menu
(parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES(0, '/', NULL, NULL, 0, NULL, 1, 0, '2019-09-15 08:08:53', NULL, NULL);
INSERT INTO menu
(parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES(1, 'User Management', NULL, 'fa fa-gears', 10, '', 1, 0, '2019-09-15 11:54:15', 1, '2019-11-06 07:21:53');
INSERT INTO menu
(parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES(2, 'User', 'user', 'fa fa-user', 10, '', 1, 0, '2019-09-15 14:09:16', NULL, '2019-12-18 01:06:52');
INSERT INTO menu
(parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES(2, 'Menu', 'menu', 'fa fa-list', 20, '', 1, 0, '2019-09-15 14:09:17', NULL, '2019-12-20 09:05:39');
INSERT INTO menu
(parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES(2, 'Access', 'access', 'fa fa-unlock-alt', 30, '', 1, 0, '2019-09-15 14:09:18', NULL, '2019-12-20 09:09:02');

INSERT INTO roleaccess
(menuid, roleid, createdby, created)
VALUES('1', 1, 0, '2024-07-17 07:36:42');
INSERT INTO roleaccess
(menuid, roleid, createdby, created)
VALUES('2', 1, 0, '2024-07-17 07:36:42');
INSERT INTO roleaccess
(menuid, roleid, createdby, created)
VALUES('3', 1, 0, '2024-07-17 07:36:42');
INSERT INTO roleaccess
(menuid, roleid, createdby, created)
VALUES('4', 1, 0, '2024-07-17 07:36:42');
INSERT INTO roleaccess
(menuid, roleid, createdby, created)
VALUES('5', 1, 0, '2024-07-17 07:36:42');

ALTER TABLE tblprintresi ADD UNIQUE (noresi);

CREATE TABLE `tblprintresihapus` (
  `id_printresihapus` int unsigned NOT NULL AUTO_INCREMENT,
  `id_printresi` int NOT NULL,
  `tanggal_printresi` datetime NOT NULL,
  `id_marketplace` int NOT NULL,
  `noresi` varchar(255) NOT NULL,
  `nomorpicklist` varchar(255) NOT NULL,
  `batal` varchar(255) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `id_kurir` int NOT NULL,
  `admin_pegawai` int NOT NULL,
  `admin_pegawai_hapus` int NOT NULL,
  `tanggal_printresi_hapus` datetime NOT NULL,
  PRIMARY KEY (`id_printresihapus`)
) ;

ALTER TABLE tblresiambilbarang ADD admin_pegawai int;
ALTER TABLE tblresiambilbarang ADD nama_komputer varchar(255);

ALTER TABLE tblresiambilbarang ADD UNIQUE (id_resi);

UPDATE tblpegawai
SET status_aktif = 'AKTIF' 
WHERE status_aktif = 'aktif';

UPDATE tblpegawai
SET status_aktif = 'TIDAK AKTIF' 
WHERE status_aktif = 'tidak aktif';

UPDATE tblnamaambilbarang
SET status_aktif = 'AKTIF' 
WHERE status_aktif = 'aktif';

UPDATE tblnamaambilbarang
SET status_aktif = 'TIDAK AKTIF' 
WHERE status_aktif = 'tidak aktif';

ALTER TABLE tblresiretur ADD id_marketplace int;
ALTER TABLE tblresiretur ADD noresi varchar(255);

CREATE TABLE `param` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paramvalue1` varchar(50) NOT NULL,
  `paramvalue2` varchar(50) DEFAULT NULL,
  `paramvalue3` varchar(50) DEFAULT NULL,
  `paramvalue4` varchar(50) DEFAULT NULL,
  `paramgroup` varchar(50) NOT NULL,
  `sortorder` int DEFAULT NULL,
  `description` text,
  `isactive` tinyint DEFAULT NULL,
  `createdby` int DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` int DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=latin1;

INSERT INTO PARAM (paramvalue1, paramgroup, sortorder, isactive, createdby, created)
VALUES ('HILANG / RUSAK PICKER', 'REPRINT_RECEIPT_REASON', 10, true, 0, now())
, ('HILANG / RUSAK PACKER', 'REPRINT_RECEIPT_REASON', 20, true, 0, now())
, ('PAKET HILANG OLEH KURIR', 'REPRINT_RECEIPT_REASON', 30, true, 0, now())
, ('PAKET TERBUANG OLEH KARYAWAN', 'REPRINT_RECEIPT_REASON', 40, true, 0, now())
, ('PAKET SALAH KERANJANG OLEH KARYAWAN', 'REPRINT_RECEIPT_REASON', 50, true, 0, now())
, ('LAINNYA', 'REPRINT_RECEIPT_REASON', 60, true, 0, now())

CREATE TABLE `tblresiprintulang` (
  `id_resiprintulang` int unsigned NOT NULL AUTO_INCREMENT,
  `tanggal_resiprintulang` datetime NOT NULL,
  `id_resi` int NOT NULL,
  `alasan` varchar(255) NOT NULL,
  `keterangan` text NOT NULL,
  `images` varchar(255) NOT NULL,
  `admin_pegawai` int NOT NULL,
  `nama_komputer` varchar(255) NOT NULL,
  PRIMARY KEY (`id_resiprintulang`)
) ENGINE=InnoDB AUTO_INCREMENT=30717329 DEFAULT CHARSET=latin1;