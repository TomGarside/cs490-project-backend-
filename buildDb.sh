#!/bin/bash 

#sets up db tables for 490 project 

sql <<EOF
 CREATE TABLE IF NOT EXISTS user (
         name VARCHAR(20),
         password VARCHAR(100),
         role VARCHAR(20)
         
         CONSTRAINT name
          PRIMARY KEY (name)

);
 CREATE TABLE IF NOT EXISTS questions(
         name VARCHAR(100),
         description VARCHAR(1000),
         difficulty VARCHAR(10),
         category   VARCHAR(10,
         testCases JSON

         CONSTRAINT name                                                                         
            PRIMARY KEY (name)  
);
 CREATE TABLE IF NOT EXISTS exam (
         name VARCHAR(100), 
         creator VARCHAR(20)

         CONSTRAINT name                                                                           
             PRIMARY KEY (name)
          
         CONSTRAINT creator 
             FOREIGN KEY creator
                REFERENCES user (name)   
);

 CREATE TABLE IF NOT EXISTS examQuestion (                                                         

          examname VARCHAR(100),                                                                 
          questionName VARCHAR(20)
                                                                       
          CONSTRAINT name 
                 PRIMARY KEY (examname questionName)
          CONSTRAINT examname
               FOREIGN KEY examname 
                  REFFERENCES exam (name)
          CONSTRAINT questionName
               FOREIGN KEY questionName
                  REFFERENCES questions (name)

CREATE TABLE IF NOT EXISTS questionResult (
         question VARCHAR(20), 
         exam VARCHAR(20),
         User VARCHAR(20), 
         answer VARCHAR(10000), 
         autograde VARCHAR(10), 
         adjustedGrade VARCHAR(10), 
         finalGrade VARCHAR(10)                                                                 
         
        CONSTRAINT find 
               PRIMARY KEY (question, exam, user)

        CONSTRAINT examname                                                                       
               FOREIGN KEY exam                                                                
                  REFFERENCES exam (name) 
                                                         
         CONSTRAINT questionName                                                                  
               FOREIGN KEY question                                                           
                  REFFERENCES questions (name)
                                                     
         CONSTRAINT user 
              FOREIGN KEY user 
                  REFFERENCES user(name)                                       
); 
 
EOF

mysql -u tg253 -p  -e sql.njit.edu 
