# blakeFez-chatRoom
在cli模式下运行   php SRC/index.php -c socket -a index 打开php  socket服务端

#create table user`
use test;
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `number` varchar(10) NOT NULL,
  `nickName` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nickname` (`nickName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;