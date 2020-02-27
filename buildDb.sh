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
         tests VARCHAR(1000)

         CONSTRAINT name                                                                         
            PRIMARY KEY (name)  
);
 CREATE TABLE IF NOT EXISTS exam (
         name VARCHAR(100), 
         creator VARCHAR(20)

         CONSTRAINT name                                                                           
             PRIMARY KEY (name)  
);

 CREATE TABLE IF NOT EXISTS examQuestion (                                                         

          examname VARCHAR(100),                                                                 
          questionName VARCHAR(20)
                                                                       
          CONSTRAINT name 
                 PRIMARY KEY (examname questionName)

CREATE TABLE IF NOT EXISTS questionResult (
         question VARCHAR(20), 
         exam VARCHAR(20),
         User VARCHAR(20), 
         answer VARCHAR(10000), 
         autograde VARCHAR(10), 
         adjustedGrade VARCHAR(10), 
         finalGrade VARCHAR(10)                                                                 
         
        CONSTRAINT name 
               PRIMARY KEY (question, exam, user) 
); 
 
EOF

mysql -u tg253 -p  -e sql.njit.edu 
