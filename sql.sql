/*
* oracle version
*/

1
create table THINK_TBCODECOLLECTION(
				 COLLECTIONID           varchar2(100) not null
				,ID                     varchar2(100) not null
				,NAME                   varchar2(100) not null
				,CODECOMMENT            varchar2(4000)
				,EDITRECORDID           varchar2(100)
				,UPNODEID               varchar2(100) 
			    ,OPERATERID             VARCHAR2(32) DEFAULT ''
			    ,OPERATERTIME			DATE DEFAULT sysdate NOT NULL
				,JLZT 					CHAR(1) DEFAULT '1' NOT NULL
				,primary key(COLLECTIONID,ID));

COMMENT ON TABLE           "BZGF"."THINK_TBCODECOLLECTION"                                   IS  '代码集数据表'
COMMENT ON COLUMN          THINK_TBCODECOLLECTION.COLLECTIONID                               IS  '代码集编号';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTION.ID                                         IS  '代码编号';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTION.NAME                                       IS  '代码名称';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTION.CODECOMMENT                                IS  '备注';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTION.EDITRECORDID                               IS  '修改记录编号';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTION.UPNODEID                                   IS  '上级代码编号';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTION.OPERATERID                                 IS  '操作员代码';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTION.OPERATERTIME	                             IS  '操作时间';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTION.JLZT 			                             IS  '记录状态';	
2
create table THINK_TBCODECOLLECTIONNAME(
				     COLLECTIONID       varchar2(100) not null primary key
				    ,COLLECTIONNAME  	varchar2(100) not null
				    ,COLLECTIONCOMMENT  varchar2(4000)
				    ,COLLECTIONLEVEL 	varchar2(100)						  
					,OPERATERID 		VARCHAR2(32) DEFAULT ''
					,OPERATERTIME 		DATE DEFAULT sysdate NOT NULL
					,JLZT 				CHAR(1) DEFAULT '1' NOT NULL
			
					);
COMMENT ON TABLE           "BZGF"."THINK_TBCODECOLLECTIONNAME"                                   IS  '代码集名称数据表';
COMMENT ON COLUMN          THINK_TBCODECOLLECTIONNAME.COLLECTIONID                               IS  '代码集编号';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTIONNAME.COLLECTIONNAME  	                         IS  '代码集名称';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTIONNAME.COLLECTIONCOMMENT                          IS  '备注';
COMMENT ON COLUMN          THINK_TBCODECOLLECTIONNAME.COLLECTIONLEVEL 	                         IS  '所属类别';
COMMENT ON COLUMN          THINK_TBCODECOLLECTIONNAME.OPERATERID 		                         IS  '操作员代码';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTIONNAME.OPERATERTIME 		                         IS  '操作时间';	
COMMENT ON COLUMN          THINK_TBCODECOLLECTIONNAME.JLZT 				                         IS  '记录状态';	
				
3
create table THINK_TBCODERULES(
					 VID 				varchar2(100) not null primary key
					,VNAME 				varchar2(100) not null
					,FILE_NAME 			varchar2(100) DEFAULT '' not null
					,TCOMMENT			varchar2(4000)
					,EDITRECORDID 		varchar2(100)			  
					,OPERATERID 		VARCHAR2(32) DEFAULT ''
					,OPERATERTIME		DATE DEFAULT sysdate NOT NULL
					,JLZT 				CHAR(1) DEFAULT '1' NOT NULL
			
			);
COMMENT ON TABLE           "BZGF"."THINK_TBCODERULES"                                  IS  '编码规则数据表';
COMMENT ON COLUMN          THINK_TBCODERULES.VID 			                           IS  '规则编号';		
COMMENT ON COLUMN          THINK_TBCODERULES.VNAME 			                           IS  '规则名称';		
COMMENT ON COLUMN          THINK_TBCODERULES.FILE_NAME 		                           IS  '文件名称';		
COMMENT ON COLUMN          THINK_TBCODERULES.TCOMMENT		                           IS  '备注';		
COMMENT ON COLUMN          THINK_TBCODERULES.EDITRECORDID 	                           IS  '修改记录编号';		
COMMENT ON COLUMN          THINK_TBCODERULES.OPERATERID 	                           IS  '操作员代码';		
COMMENT ON COLUMN          THINK_TBCODERULES.OPERATERTIME	                           IS  '操作时间';		
COMMENT ON COLUMN          THINK_TBCODERULES.JLZT 			                           IS  '记录状态';		
	

 4  
create table THINK_TBINFOCLASS(
					 ICLASSID 			varchar2(100) not null
					,VID				varchar2(100) not null primary key
					,VNAME				varchar2(100) not null
					,VNAMECHN 			varchar2(100) not null
					,VTYPE 				varchar2(100) not null
					,ILENGTH 			int not null
					,VSELECT 			varchar2(10)
					,VVALUESCOPE 		varchar2(1024)
					,TCOMMENT 			varchar2(4000)
					,VREF 				varchar2(1024)
					,EDITRECORDID 		varchar2(100)			  
					,OPERATERID 		VARCHAR2(32) DEFAULT ''
					,OPERATERTIME 		DATE DEFAULT sysdate NOT NULL
					,JLZT 				CHAR(1) 		DEFAULT '1' NOT NULL
						 
					 );
					 
COMMENT ON TABLE           "BZGF"."THINK_TBINFOCLASS"                                  IS  '数据子类数据表';
COMMENT ON COLUMN          THINK_TBINFOCLASS.ICLASSID 		                           IS  '数据子类编号';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.VID			                           IS  '子类项编号';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.VNAME			                           IS  '子类项名称';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.VNAMECHN 		                           IS  '子类项中文名称';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.VTYPE 			                           IS  '数据类型';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.ILENGTH 		                           IS  '长度';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.VSELECT 		                           IS  '是否可选';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.VVALUESCOPE 	                           IS  '取值范围';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.TCOMMENT 		                           IS  '备注';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.VREF 			                           IS  '引用';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.EDITRECORDID 	                           IS  '修改记录编号';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.OPERATERID 	                           IS  '操作员代码';				 
COMMENT ON COLUMN          THINK_TBINFOCLASS.OPERATERTIME 	                           IS  '操作时间';					 
COMMENT ON COLUMN          THINK_TBINFOCLASS.JLZT 			                           IS  '记录状态';					 
					 
5
create table THINK_TBINFOCLASSNAME(
						 ICLASSID 			varchar2(100) not null primary key
						,VCLASSNAME 		varchar2(100) not null
						,TCOMMENT 			varchar2(4000)
						,VCLASSLEVEL 		varchar2(100)			  
						,OPERATERID 		VARCHAR2(32) DEFAULT ''
						,OPERATERTIME 		DATE DEFAULT sysdate NOT NULL
						,JLZT 				CHAR(1) 	DEFAULT '1' NOT NULL
						
				);
COMMENT ON TABLE           "BZGF"."THINK_TBINFOCLASSNAME"                                      IS  '数据子类名称数据表';
COMMENT ON COLUMN          THINK_TBINFOCLASSNAME.ICLASSID 		                               IS  '数据子类编号';	
COMMENT ON COLUMN          THINK_TBINFOCLASSNAME.VCLASSNAME 		                           IS  '数据子类名称';	
COMMENT ON COLUMN          THINK_TBINFOCLASSNAME.TCOMMENT 			                           IS  '备注';	
COMMENT ON COLUMN          THINK_TBINFOCLASSNAME.VCLASSLEVEL 		                           IS  '所属类别';	
COMMENT ON COLUMN          THINK_TBINFOCLASSNAME.OPERATERID 		                           IS  '操作员代码';	
COMMENT ON COLUMN          THINK_TBINFOCLASSNAME.OPERATERTIME 		                           IS  '操作时间';		
COMMENT ON COLUMN          THINK_TBINFOCLASSNAME.JLZT 				                           IS  '记录状态';		
  6                                  
create table THINK_TBUNIQUEID(
						id 					varchar2(100)
						);
						
COMMENT ON TABLE           "BZGF"."THINK_TBUNIQUEID"                                      IS  '唯一性ID数据表';				
7
create table THINK_TBCHANGERECORD(
					 VTIME 				varchar2(100)
					,VAUTHOR 			varchar2(100)
					,VPARENTPAGE 		varchar2(100)
					,VACTION 			varchar2(100)
					,ITEMID 			varchar2(100)
					,VFIELDNAME 		varchar2(100)
					,VOLDCONTENT 		varchar2(4000)
					,VNEWCONTENT 		varchar2(4000)
					,EDITID 			varchar2(100)		  
					,OPERATERID 		VARCHAR2(32) DEFAULT ''
					,OPERATERTIME 		DATE DEFAULT sysdate NOT NULL
					,JLZT 				CHAR(1) DEFAULT '1' NOT NULL					
					,primary key(VTIME,VAUTHOR,VPARENTPAGE,VACTION,VFIELDNAME,ITEMID)
			
			);
			
COMMENT ON TABLE           "BZGF"."THINK_TBCHANGERECORD"                                       IS  '修改记录数据表';
COMMENT ON COLUMN          THINK_TBCHANGERECORD.VTIME 			                               IS  '修改时间';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.VAUTHOR 		                               IS  '修改人';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.VPARENTPAGE 	                               IS  '修改记录';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.VACTION 		                               IS  '修改操作';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.ITEMID 		                                   IS  '修改项编号';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.VFIELDNAME 	                                   IS  '修改字段';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.VOLDCONTENT 	                               IS  '修改前内容';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.VNEWCONTENT 	                               IS  '修改后内容';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.EDITID 		                                   IS  '修改记录编号';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.OPERATERID 	                                   IS  '操作员代码';				
COMMENT ON COLUMN          THINK_TBCHANGERECORD.OPERATERTIME 	                               IS  '操作时间';					
COMMENT ON COLUMN          THINK_TBCHANGERECORD.JLZT 			                               IS  '记录状态';					
			
8
create table THINK_TBEDITRECORD(
					 VTIME 				varchar2(100)
					,VAUTHOR 			varchar2(100)
					,TCONTENT			varchar2(4000)
					,EDITID 			varchar2(100)		  
					,OPERATERID 		VARCHAR2(32) DEFAULT ''
					,OPERATERTIME 		DATE DEFAULT sysdate NOT NULL
					,JLZT 				CHAR(1) DEFAULT '1' NOT NULL
					
					,primary key(VTIME,VAUTHOR,TCONTENT,EDITID));
					
COMMENT ON TABLE           "BZGF"."THINK_TBEDITRECORD"                                             IS  '修改通知数据表';
COMMENT ON COLUMN          THINK_TBEDITRECORD.VTIME 					                           IS  '修改时间';						
COMMENT ON COLUMN          THINK_TBEDITRECORD.VAUTHOR 				                               IS  '修改人';						
COMMENT ON COLUMN          THINK_TBEDITRECORD.TCONTENT				                               IS  '修改内容';						
COMMENT ON COLUMN          THINK_TBEDITRECORD.EDITID 				                               IS  '修改记录编号';						
COMMENT ON COLUMN          THINK_TBEDITRECORD.OPERATERID 			                               IS  '操作员代码';						
COMMENT ON COLUMN          THINK_TBEDITRECORD.OPERATERTIME 			                               IS  '操作时间';							
COMMENT ON COLUMN          THINK_TBEDITRECORD.JLZT 					                               IS  '记录状态';							
					
9								
create table THINK_TBCUSTOMCLASSES(
					 ICLASSID 			varchar2(100) not null primary key
					,VNAME 				varchar2(100)
					,TCOMMENT 			varchar2(4000)
					,VCLASSLEVEL 		varchar2(100)		
					,OPERATERID 		VARCHAR2(32) DEFAULT ''
					,OPERATERTIME 		DATE DEFAULT sysdate NOT NULL
					,JLZT 				CHAR(1) DEFAULT '1' NOT NULL			
			);
			
COMMENT ON TABLE           "BZGF"."THINK_TBCUSTOMCLASSES"                                              IS  '自定义项目名称数据表';
COMMENT ON COLUMN          THINK_TBCUSTOMCLASSES.ICLASSID 					                           IS  '自定义项目编号';				
COMMENT ON COLUMN          THINK_TBCUSTOMCLASSES.VNAME 						                           IS  '项目名称';				
COMMENT ON COLUMN          THINK_TBCUSTOMCLASSES.TCOMMENT 					                           IS  '备注';				
COMMENT ON COLUMN          THINK_TBCUSTOMCLASSES.VCLASSLEVEL 				                           IS  '所属类别';				
COMMENT ON COLUMN          THINK_TBCUSTOMCLASSES.OPERATERID 				                           IS  '操作员代码';			
COMMENT ON COLUMN          THINK_TBCUSTOMCLASSES.OPERATERTIME 				                           IS  '操作时间';				
COMMENT ON COLUMN          THINK_TBCUSTOMCLASSES.JLZT 						                           IS  '记录状态';				
			
			
10								
create table THINK_TBCUSTOMITEMS(
					 ICLASSID 			varchar2(100)
					,VITEMID 			varchar2(100)
					,VITEMNAME 			varchar2(100)
					,VITEMCONTENT 		varchar2(4000)
					,TCOMMENT 			varchar2(4000)
					,EDITRECORDID 		varchar2(100)
					,VCLASSLEVEL 		varchar2(100)	
					,OPERATERID 		VARCHAR2(32) DEFAULT ''
					,OPERATERTIME 		DATE DEFAULT sysdate NOT NULL
					,JLZT 				CHAR(1) DEFAULT '1' NOT NULL
					
					,primary key(VITEMID,ICLASSID)
			
			);
					
COMMENT ON TABLE           "BZGF"."THINK_TBCUSTOMITEMS"                                                IS  '自定义项目条目数据表';
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.ICLASSID 					                           IS  '自定义项目编号';	
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.VITEMID 					                               IS  '条目编号';	
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.VITEMNAME 					                           IS  '条目名称';	
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.VITEMCONTENT 				                           IS  '条目内容';	
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.TCOMMENT 					                           IS  '备注';	
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.EDITRECORDID 				                           IS  '修改记录编号';	
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.VCLASSLEVEL 				                               IS  '所属类别';	
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.OPERATERID 				                               IS  '操作员代码';	
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.OPERATERTIME 				                           IS  '操作时间';		
COMMENT ON COLUMN          THINK_TBCUSTOMITEMS.JLZT 						                           IS  '记录状态';		
					
11
CREATE TABLE THINK_USER (
					 ACCOUNT        varchar2(100) NOT NULL
					,NICKNAME       varchar2(100) NOT NULL
					,PASSWORD       varchar2(32) NOT NULL
					,REMARK         varchar2(255)  NULL
					,EMAIL          varchar2(100) NULL  
					,OPERATERID     VARCHAR2(32) DEFAULT ''
					,OPERATERTIME   DATE DEFAULT sysdate NOT NULL
					,JLZT           CHAR(1) DEFAULT '1' NOT NULL
					
					,PRIMARY KEY  (ACCOUNT)
) ;

COMMENT ON TABLE           "BZGF"."THINK_USER"                                                IS  '用户数据表';
COMMENT ON COLUMN          THINK_USER.ACCOUNT     			                              IS  '账号';
COMMENT ON COLUMN          THINK_USER.NICKNAME    			                              IS  '名称';
COMMENT ON COLUMN          THINK_USER.PASSWORD    			                              IS  '密码';
COMMENT ON COLUMN          THINK_USER.REMARK      			                              IS  '';
COMMENT ON COLUMN          THINK_USER.EMAIL       			                              IS  '邮件';
COMMENT ON COLUMN          THINK_USER.OPERATERID  			                              IS  '操作员代码';
COMMENT ON COLUMN          THINK_USER.OPERATERTIME			                              IS  '操作时间';	
COMMENT ON COLUMN          THINK_USER.JLZT        			                              IS  '记录状态';	


12
CREATE TABLE THINK_ROLE (
				 NAME              varchar2(100)
				,STATUS            int
				,REMARK            varchar2(255) NULL
				,OPERATERID        VARCHAR2(32) DEFAULT ''
				,OPERATERTIME      DATE DEFAULT sysdate NOT NULL
				,JLZT              CHAR(1) DEFAULT '1' NOT NULL
					
				,PRIMARY KEY  (NAME)
) ;

COMMENT ON TABLE           "BZGF"."THINK_ROLE"                                            IS  '用户角色数据表';
COMMENT ON COLUMN          THINK_ROLE.NAME           			                              IS  '角色名称';
COMMENT ON COLUMN          THINK_ROLE.STATUS         			                              IS  '';
COMMENT ON COLUMN          THINK_ROLE.REMARK         			                              IS  '中文名称';
COMMENT ON COLUMN          THINK_ROLE.OPERATERID     			                              IS  '操作员代码';
COMMENT ON COLUMN          THINK_ROLE.OPERATERTIME   			                              IS  '操作时间';	
COMMENT ON COLUMN          THINK_ROLE.JLZT           			                              IS  '记录状态';	

13
CREATE TABLE THINK_ROLEUSER (
				 ROLE_NAME        varchar2(100)
				,USER_ACOUNT      varchar2(100)
				,OPERATERID       VARCHAR2(32) DEFAULT ''
				,OPERATERTIME     DATE DEFAULT sysdate NOT NULL
				,JLZT             CHAR(1) DEFAULT '1' NOT NULL

				,primary key(ROLE_NAME,USER_ACOUNT)
) ;

COMMENT ON TABLE           "BZGF"."THINK_ROLEUSER"                                                IS  '用户角色链接数据表';
COMMENT ON COLUMN          THINK_ROLEUSER.ROLE_NAME            			                              IS  '角色名称';
COMMENT ON COLUMN          THINK_ROLEUSER.USER_ACOUNT          			                              IS  '用户账号';
COMMENT ON COLUMN          THINK_ROLEUSER.OPERATERID           			                              IS  '操作员代码';
COMMENT ON COLUMN          THINK_ROLEUSER.OPERATERTIME         			                              IS  '操作时间';	
COMMENT ON COLUMN          THINK_ROLEUSER.JLZT                 			                              IS  '记录状态';	

14
CREATE TABLE T_INTERFACES_FILE (
					 FILE_NAME 		varchar2(100)
					,UPLOAD_DATE 	varchar2(100)
					,FILE_SIZE 		varchar2(100)
					,AUTHOR 		varchar2(100)			  
					,OPERATERID 	VARCHAR2(32) DEFAULT ''
					,OPERATERTIME 	DATE DEFAULT sysdate NOT NULL
					,JLZT 			CHAR(1) DEFAULT '1' NOT NULL
					
					,PRIMARY KEY(FILE_NAME)
) ;

COMMENT ON TABLE           "BZGF"."T_INTERFACES_FILE"                                                     IS  '接口规范数据表';
COMMENT ON COLUMN          T_INTERFACES_FILE.FILE_NAME 		        			                              IS  '规范名称';
COMMENT ON COLUMN          T_INTERFACES_FILE.UPLOAD_DATE 	        			                              IS  '上传时间';
COMMENT ON COLUMN          T_INTERFACES_FILE.FILE_SIZE 		        			                              IS  '大小';
COMMENT ON COLUMN          T_INTERFACES_FILE.AUTHOR 		        			                              IS  '上传人';
COMMENT ON COLUMN          T_INTERFACES_FILE.OPERATERID 	        			                              IS  '操作员代码';
COMMENT ON COLUMN          T_INTERFACES_FILE.OPERATERTIME 	        			                              IS  '操作时间';	
COMMENT ON COLUMN          T_INTERFACES_FILE.JLZT 			        			                              IS  '记录状态';	

15
CREATE TABLE T_ORDER_ITEM (
					 USER_ACOUNT 	varchar2(100)
					,ITEM_NAME 		varchar2(100)			  
					,OPERATERID 	VARCHAR2(32) DEFAULT ''
					,OPERATERTIME 	DATE DEFAULT sysdate NOT NULL
					,JLZT 			CHAR(1) 	DEFAULT '1' NOT NULL
			
					,PRIMARY KEY(USER_ACOUNT,ITEM_NAME)
) ;

COMMENT ON TABLE           "BZGF"."T_ORDER_ITEM"                                                     IS  '用户订阅数据表';
COMMENT ON COLUMN          T_ORDER_ITEM.USER_ACOUNT 		        			                              IS  '用户账号';
COMMENT ON COLUMN          T_ORDER_ITEM.ITEM_NAME 			        			                              IS  '订阅项目名称';
COMMENT ON COLUMN          T_ORDER_ITEM.OPERATERID 		        			                                  IS  ''操作员代码';
COMMENT ON COLUMN          T_ORDER_ITEM.OPERATERTIME 		        			                              IS  ''操作时间';	
COMMENT ON COLUMN          T_ORDER_ITEM.JLZT 				        			                              IS  ''记录状态';	

16
CREATE TABLE T_CONFIG (
				 VKEY 		varchar2(100)
				,VVALUE     varchar2(100)
				,PRIMARY KEY(VKEY)
) ;

COMMENT ON TABLE           "BZGF"."T_CONFIG"                                                     IS  '系统配置数据表';
COMMENT ON COLUMN          T_CONFIG.VKEY 		        			                              IS  '配置项名称';
COMMENT ON COLUMN          T_CONFIG.VVALUE 		        			                              IS  '配置项值';

17
create table T_MAIL_LOG(
MAIL_TIME VARCHAR2(32) NOT NULL PRIMARY KEY
);
insert into T_INTERFACES_FILE(FILE_NAME,UPLOAD_DATE,FILE_SIZE,AUTHOR) values();
insert into THINK_ROLE(NAME,REMARK) values('editor','高级用户') ;
insert into THINK_ROLE(NAME,REMARK) values('viewer','普通用户') ;
insert into THINK_ROLE(NAME,REMARK) values('adminer','系统管理员') ;

insert into THINK_USER(ACCOUNT,NICKNAME,PASSWORD) values('admin','管理员','202cb962ac59075b964b07152d234b70') ;
insert into THINK_USER(ACCOUNT,NICKNAME,PASSWORD) values('leader','高级用户','202cb962ac59075b964b07152d234b70'); 
insert into THINK_USER(ACCOUNT,NICKNAME,PASSWORD) values('demo','普通用户','202cb962ac59075b964b07152d234b70') ;

insert into THINK_ROLEUSER(ROLE_NAME,USER_ACOUNT) values('editor','leader');
insert into THINK_ROLEUSER(ROLE_NAME,USER_ACOUNT) values('viewer','demo');
insert into THINK_ROLEUSER(ROLE_NAME,USER_ACOUNT) values('adminer','admin');

SELECT VTIME,VAUTHOR,TCONTENT,EDITID FROM THINK_TBEDITRECORD where VTIME>'2011-10-30 00:00:00' and VTIME < '2011-11-01 00:00:00'

// mysql version
1
CREATE TABLE T_ORDER_ITEM (
			USER_ACOUNT varchar(100)
			,ITEM_NAME varchar(100)
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
			,PRIMARY KEY(USER_ACOUNT,ITEM_NAME)
) ;
2
CREATE TABLE T_INTERFACES_FILE (
			FILE_NAME varchar(100)
			,UPLOAD_DATE varchar(100)
			,FILE_SIZE varchar(100)
			,AUTHOR varchar(100)
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
			,PRIMARY KEY(FILE_NAME)
) ;
3
create table THINK_TBCODECOLLECTION(
				COLLECTIONID varchar(100) not null
				,ID varchar(100) not null
				,NAME varchar(100) not null
				,CODECOMMENT text
				,EDITRECORDID varchar(100)
				,UPNODEID varchar(100) 
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
				,primary key(COLLECTIONID,ID));
4
create table THINK_TBCODECOLLECTIONNAME(
				    COLLECTIONID varchar(100) not null primary key
				    ,COLLECTIONNAME varchar(100) not null
				    ,COLLECTIONCOMMENT text
				    ,COLLECTIONLEVEL varchar(100)
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
					);
5
create table THINK_TBCODERULES(
			 VID                    varchar(100) not null primary key
			,VNAME             		varchar(100) not null
			,FILE_NAME				VARCHAR(100) DEFAULT '' NOT NULL
			,TCOMMENT 				text
			,EDITRECORDID varchar(100)
			,OPERATERID 			VARCHAR(32) default ''  NOT NULL
			,OPERATERTIME 			VARCHAR(32) DEFAULT (date('now')) NOT NULL
			,JLZT 					VARCHAR(1) DEFAULT '1' NOT NULL
			);
create table THINK_TBCODERULES(
			VID varchar(100) not null primary key
			,VNAME varchar(100) not null
			,VNAMECHN varchar(100) not null
			,VTYPE varchar(100) not null
			,NLENGTH int not null
			,TCOMMENT text
			,EDITRECORDID varchar(100)
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
			);
6   
create table THINK_TBINFOCLASS(
			ICLASSID varchar(100) not null
			,VID varchar(100) not null primary key
			,VNAME varchar(100) not null
			,VNAMECHN varchar(100) not null
			,VTYPE varchar(100) not null
			,ILENGTH int not null
			,VSELECT varchar(10)
			,VVALUESCOPE varchar(1024)
			,TCOMMENT text
			,VREF varchar(1024)
			 ,EDITRECORDID varchar(100) 
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
					 );
7
create table THINK_TBINFOCLASSNAME(
			    ICLASSID varchar(100) not null primary key
			    ,VCLASSNAME varchar(100) not null
			    ,TCOMMENT text
			    ,VCLASSLEVEL varchar(100)
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
				);
8                                    
create table THINK_TBUNIQUEID(id varchar(100));
9
create table THINK_TBEDITRECORD(
			VTIME varchar(100),
			VAUTHOR varchar(100),
			TCONTENT text,
			EDITID varchar(100)
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
			,primary key(VTIME,VAUTHOR,TCONTENT,EDITID));
10
create table THINK_TBCUSTOMCLASSES(
			ICLASSID varchar(100) not null primary key,
			VNAME varchar(100),
			TCOMMENT varchar(4000)
			,VCLASSLEVEL varchar(100)
			,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
			);
11				
create table THINK_TBCUSTOMITEMS(
			ICLASSID varchar(100),
			VITEMID varchar(100),
			VITEMNAME varchar(100),
			VITEMCONTENT text,
			TCOMMENT text,
			EDITRECORDID varchar(100)
			,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
			,primary key(VITEMID,ICLASSID));
12
CREATE TABLE THINK_USER (
		  ACCOUNT varchar(100) NOT NULL,
		  NICKNAME varchar(100) NOT NULL,
		  PASSWORD varchar(32) NOT NULL,
		  REMARK varchar(255)  NULL,
		  EMAIL varchar2(100) NULL,
		  OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL,
		  PRIMARY KEY  (ACCOUNT)
) ;
13
CREATE TABLE THINK_ROLE (
		NAME varchar(100)
		,STATUS int
		,REMARK varchar(255) NULL
				  ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
		,PRIMARY KEY  (NAME)
) ;
14
CREATE TABLE THINK_ROLEUSER (
			ROLE_NAME varchar(100)
			,USER_ACOUNT varchar(100)
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
			,primary key(ROLE_NAME,USER_ACOUNT)
) ;
15
create table T_MAIL_LOG(
MAIL_TIME VARCHAR(32) NOT NULL PRIMARY KEY
);
16
CREATE TABLE T_CONFIG (
			VKEY varchar(100)
			,VVALUE varchar(100)
			,PRIMARY KEY(VKEY)
) ;
17
create table THINK_TBCHANGERECORD(
			VTIME varchar(100),
			VAUTHOR varchar(100),
			VPARENTPAGE varchar(100),
			VACTION varchar(100),
			ITEMID varchar(100),
			VFIELDNAME varchar(100),
			VOLDCONTENT varchar(4000),
			VNEWCONTENT varchar(4000),
			EDITID varchar(100)
			 ,OPERATERID VARCHAR(32) default ''  NOT NULL,
			OPERATERTIME VARCHAR(32) DEFAULT (date('now')) NOT NULL,
			JLZT VARCHAR(1) DEFAULT '1' NOT NULL
			,primary key(VTIME,VAUTHOR,VPARENTPAGE,VACTION,VFIELDNAME,ITEMID));
			
			
			
insert into THINK_ROLE(NAME,REMARK) values('editor','高级用户') ;
insert into THINK_ROLE(NAME,REMARK) values('viewer','普通用户') ;
insert into THINK_ROLE(NAME,REMARK) values('adminer','系统管理员') ;

insert into THINK_USER(ACCOUNT,NICKNAME,PASSWORD) values('admin','管理员','202cb962ac59075b964b07152d234b70') ;
insert into THINK_USER(ACCOUNT,NICKNAME,PASSWORD) values('leader','高级用户','202cb962ac59075b964b07152d234b70'); 
insert into THINK_USER(ACCOUNT,NICKNAME,PASSWORD) values('demo','普通用户','202cb962ac59075b964b07152d234b70') ;

insert into THINK_ROLEUSER(ROLE_NAME,USER_ACOUNT) values('editor','leader');
insert into THINK_ROLEUSER(ROLE_NAME,USER_ACOUNT) values('viewer','demo');
insert into THINK_ROLEUSER(ROLE_NAME,USER_ACOUNT) values('adminer','admin');


