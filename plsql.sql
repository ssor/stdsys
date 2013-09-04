CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_USER"
 after insert or   update on "BZGF"."THINK_USER"
  for each row
begin
      insert into HL_T_USER(
                     HID
                    ,ACCOUNT
                    ,NICKNAME
                    ,PASSWORD
                    ,REMARK     
                    ,EMAIL
                    ,OPERATERID
                    ,OPERATERTIME
                    ,JLZT )
                 values
                (
                     SYS_GUID()
                    ,:new.ACCOUNT    
                    ,:new.NICKNAME   
                    ,:new.PASSWORD   
                    ,:new.REMARK     
                    ,:new.EMAIL      
                    ,:new.OPERATERID 
                    ,:new.OPERATERTIME
                    ,:new.JLZT
                );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_ROLE"
 after insert or   update on "BZGF"."THINK_ROLE"
  for each row
begin
      insert into HL_T_ROLE(
                       HID
                      ,NAME        
                      ,STATUS     
                      ,REMARK       
                      ,OPERATERID 
                      ,OPERATERTIME       
                      ,JLZT 
                            )
              values(
                     SYS_GUID()
                    ,:new.NAME        
                    ,:new.STATUS     
                    ,:new.REMARK      
                    ,:new.OPERATERID 
                    ,:new.OPERATERTIME
                    ,:new.JLZT 
                    );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_ROLEUSER"
 after insert or   update on "BZGF"."THINK_ROLEUSER"
  for each row
begin
      insert into HL_T_ROLEUSER(
                         HID
                        ,ROLE_NAME    
                        ,USER_ACOUNT 
                        ,OPERATERID   
                        ,OPERATERTIME
                        ,JLZT               
                                )
              values(
                         SYS_GUID()
                        ,:new.ROLE_NAME   
                        ,:new.USER_ACOUNT 
                        ,:new.OPERATERID  
                        ,:new.OPERATERTIME
                        ,:new.JLZT        
                     );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_TBCHANGERECORD"
 after insert or   update on "BZGF"."THINK_TBCHANGERECORD"
  for each row
begin
      insert into HL_T_TBCHANGERECORD(
                         HID
                        ,VTIME 			 
                        ,VAUTHOR 		
                        ,VPARENTPAGE 	 
                        ,VACTION 		
                        ,ITEMID 		
                        ,VFIELDNAME 	
                        ,VOLDCONTENT 	
                        ,VNEWCONTENT 	
                        ,EDITID 		
                        ,OPERATERID 	
						,OPERATERTIME 	
                        ,JLZT 
						)
              values(
                         SYS_GUID()
                        ,:new.VTIME 			
                        ,:new.VAUTHOR 		
                        ,:new.VPARENTPAGE 	
                        ,:new.VACTION 		
                        ,:new.ITEMID 		
                        ,:new.VFIELDNAME 	
                        ,:new.VOLDCONTENT 	
                        ,:new.VNEWCONTENT 	
                        ,:new.EDITID 		
                        ,:new.OPERATERID 	
                        ,:new.OPERATERTIME 	
                        ,:new.JLZT 			
                     );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_TBCODECOLLECTION"
 after insert or   update on "BZGF"."THINK_TBCODECOLLECTION"
  for each row
begin
      insert into HL_T_TBCODECOLLECTION(
                         HID
                        ,COLLECTIONID    
                        ,ID             
                        ,NAME            
                        ,CODECOMMENT    
                        ,EDITRECORDID   
                        ,UPNODEID       
                        ,OPERATERID     
                        ,OPERATERTIME	
                        ,JLZT 			
						)
              values(
                         SYS_GUID()
                        ,:new.COLLECTIONID   	
                        ,:new.ID             
                        ,:new.NAME           
                        ,:new.CODECOMMENT    
                        ,:new.EDITRECORDID   
                        ,:new.UPNODEID       
                        ,:new.OPERATERID     
                        ,:new.OPERATERTIME	
                        ,:new.JLZT 			
                     );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_TBCODECOLLECTIONNAME"
 after insert or   update on "BZGF"."THINK_TBCODECOLLECTIONNAME"
  for each row
begin
      insert into HL_T_TBCODECOLLECTIONNAME(
                         HID
                        ,COLLECTIONID        
                        ,COLLECTIONNAME  	
                        ,COLLECTIONCOMMENT   
                        ,COLLECTIONLEVEL 	
                        ,OPERATERID 		
                        ,OPERATERTIME 		
                        ,JLZT 				
						)
              values(
                         SYS_GUID()
                        ,:new.COLLECTIONID        	
                        ,:new.COLLECTIONNAME  	 
                        ,:new.COLLECTIONCOMMENT   
                        ,:new.COLLECTIONLEVEL 	 
                        ,:new.OPERATERID 		 
                        ,:new.OPERATERTIME 		 
                        ,:new.JLZT 				 
                     );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_TBCODERULES"
 after insert or   update on "BZGF"."THINK_TBCODERULES"
  for each row
begin
      insert into HL_T_TBCODERULES(
                         HID
                        ,VID 			 
                        ,VNAME 			
                        ,FILE_NAME 		 		
                        ,TCOMMENT		
                        ,EDITRECORDID 	
                        ,OPERATERID 	
                        ,OPERATERTIME	
						,JLZT
						)
              values(
                         SYS_GUID()
                        ,:new.VID 			  	
                        ,:new.VNAME 			 
                        ,:new.FILE_NAME 		  	 
                        ,:new.TCOMMENT		 
                        ,:new.EDITRECORDID 	 
                        ,:new.OPERATERID 	 
                        ,:new.OPERATERTIME	 
                        ,:new.JLZT 			 
                     );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_TBCUSTOMCLASSES"
 after insert or   update on "BZGF"."THINK_TBCUSTOMCLASSES"
  for each row
begin
      insert into HL_T_TBCUSTOMCLASSES(
                         HID
                        ,ICLASSID 		 
                        ,VNAME 			
                        ,TCOMMENT 		 
                        ,VCLASSLEVEL 	
                        ,OPERATERID 	
                        ,OPERATERTIME 	
                        ,JLZT 			
						)
              values(
                         SYS_GUID()
                        ,:new.ICLASSID 		  	
                        ,:new.VNAME 				 
                        ,:new.TCOMMENT 		  
                        ,:new.VCLASSLEVEL 		 
                        ,:new.OPERATERID 	 
                        ,:new.OPERATERTIME 	 
                        ,:new.JLZT 			 
                     );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_TBCUSTOMITEMS"
 after insert or   update on "BZGF"."THINK_TBCUSTOMITEMS"
  for each row
begin
      insert into HL_T_TBCUSTOMITEMS(
                         HID
                        ,ICLASSID 		 
                        ,VITEMID 		
                        ,VITEMNAME 		 
                        ,VITEMCONTENT 	
                        ,TCOMMENT 		
                        ,EDITRECORDID 	
                        ,VCLASSLEVEL 	
                        ,OPERATERID 	
                        ,OPERATERTIME 	
						,JLZT
						)
              values(
                         SYS_GUID()
                        ,:new.ICLASSID 		  	
                        ,:new.VITEMID 				 
                        ,:new.VITEMNAME 		  
                        ,:new.VITEMCONTENT 		 
                        ,:new.TCOMMENT 		 
                        ,:new.EDITRECORDID 	 
                        ,:new.VCLASSLEVEL 	 
                        ,:new.OPERATERID 	 
                        ,:new.OPERATERTIME 	 
                        ,:new.JLZT 			 
                     );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_TBEDITRECORD"
 after insert or   update on "BZGF"."THINK_TBEDITRECORD"
  for each row
begin
      insert into HL_T_TBEDITRECORD(
                         HID
                        ,VTIME 			
                        ,VAUTHOR 		
                        ,TCONTENT		
                        ,EDITID 		
                        ,OPERATERID 	
                        ,OPERATERTIME 	
                        ,JLZT 			
						)
              values(
                         SYS_GUID()
                        ,:new.VTIME 			  	
                        ,:new.VAUTHOR 				 
                        ,:new.TCONTENT			  
                        ,:new.EDITID 			 
                        ,:new.OPERATERID 	 
                        ,:new.OPERATERTIME 	 
                        ,:new.JLZT 			 
                     );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_TBINFOCLASS"
 after insert or   update on "BZGF"."THINK_TBINFOCLASS"
  for each row
begin
      insert into HL_T_TBINFOCLASS(
                         HID
                        ,ICLASSID 		
                        ,VID			
                        ,VNAME			
                        ,VNAMECHN 		
                        ,VTYPE 			
                        ,ILENGTH 		
                        ,VSELECT 		
                        ,VVALUESCOPE 	
                        ,TCOMMENT 		
                        ,VREF 			
                        ,EDITRECORDID 	
                        ,OPERATERID 	
                        ,OPERATERTIME 	
                        ,JLZT 			
						)
              values(
                         SYS_GUID()
                        ,:new.ICLASSID 			  	
                        ,:new.VID					 
                        ,:new.VNAME				  
                        ,:new.VNAMECHN 			 
                        ,:new.VTYPE 			
                        ,:new.ILENGTH 		
                        ,:new.VSELECT 		
                        ,:new.VVALUESCOPE 	
                        ,:new.TCOMMENT 		
                        ,:new.VREF 			
                        ,:new.EDITRECORDID 	
                        ,:new.OPERATERID 	
                        ,:new.OPERATERTIME 	
                        ,:new.JLZT 			
                     );
end;

CREATE OR REPLACE TRIGGER "BZGF"."TRI_AUDIT_TBINFOCLASSNAME"
 after insert or   update on "BZGF"."THINK_TBINFOCLASSNAME"
  for each row
begin
      insert into HL_T_TBINFOCLASSNAME(
                         HID
                        ,ICLASSID 		
                        ,VCLASSNAME 	
                        ,TCOMMENT 		
                        ,VCLASSLEVEL 	
                        ,OPERATERID 	
                        ,OPERATERTIME 	
                        ,JLZT 			
						)
              values(
                         SYS_GUID()
                        ,:new.ICLASSID 			  	
                        ,:new.VCLASSNAME 			 
                        ,:new.TCOMMENT 			  
                        ,:new.VCLASSLEVEL 		 
                        ,:new.OPERATERID 		
                        ,:new.OPERATERTIME 	
                        ,:new.JLZT 			
                     );
end;
