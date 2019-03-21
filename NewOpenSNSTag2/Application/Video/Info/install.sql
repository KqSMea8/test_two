-- -----------------------------
-- 表结构 `ocenter_video`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `ocenter_video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `allow_post` tinyint(4) NOT NULL,
  `pid` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;


-- -----------------------------
-- 表结构 `ocenter_video_content`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `ocenter_video_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `view_count` int(11) NOT NULL COMMENT '阅读数量',
  `cover_id` int(11) NOT NULL COMMENT '封面图片id',
  `video_id` int(11) NOT NULL COMMENT '所在视频',
  `uid` int(11) NOT NULL COMMENT '发布者id',
  `reply_count` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(11) NOT NULL,
  `url` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='视频内容表';

-- -----------------------------
-- 表内记录 `ocenter_video`
-- -----------------------------
INSERT INTO `ocenter_video` VALUES ('13', '默认栏目', '1409712474', '1409712467', '1', '0', '0', '0');
INSERT INTO `ocenter_video` VALUES ('14', '默认二级', '1409712480', '1409712475', '1', '0', '13', '0');
-- -----------------------------
-- 表内记录 `ocenter_video_content`
-- -----------------------------
INSERT INTO `ocenter_video_content` VALUES ('29', '测试视频', '你大爷的你大爷的你大爷的你大爷的你大爷的你大爷的', '6', '21', '14', '1', '0', '1430704938', '1430704938', '1', 'http://player.youku.com/player.php/sid/XMTI3NjYxMjkyNA==/v.swf');
INSERT INTO `ocenter_video_content` VALUES ('30', '测试视频发布', '测试视频发布测试视频发布测试视频发布测试视频发布测试视频发布测试视频发布测试视频发布测试视频发布测试视频发布', '5', '22', '14', '1', '0', '1430705543', '1430705543', '1', 'http://player.youku.com/player.php/sid/XMTI3Nzk5NjM4OA==/v.swf');
