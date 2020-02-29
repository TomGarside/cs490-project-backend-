#!/bin/bash 

#sets up db tables for 490 project 

#expects thease from std in can pass file 
read host
read username 
read password

mysql -u $username -p$password  -h $host -D tg253  <<EOF
 CREATE TABLE IF NOT EXISTS user (
         name VARCHAR(20),
         password VARCHAR(100),
         role VARCHAR(20),
         PRIMARY KEY (name)
);

 CREATE TABLE IF NOT EXISTS questions(
         name VARCHAR(100),
         description VARCHAR(1000),
         difficulty VARCHAR(100),
         category   VARCHAR(100),
         testCases JSON,
         PRIMARY KEY (name)
);
 CREATE TABLE IF NOT EXISTS exam (
         name VARCHAR(100), 
         creator VARCHAR(20),
         PRIMARY KEY (name),
         FOREIGN KEY (creator) REFERENCES user (name)   
);

 CREATE TABLE IF NOT EXISTS examQuestion (                                                     
          examname VARCHAR(100),                                                             
          questionName VARCHAR(100),
          PRIMARY KEY (examname, questionName),
          FOREIGN KEY (examname) REFERENCES exam (name),
          FOREIGN KEY (questionName) REFERENCES questions (name)
);
CREATE TABLE IF NOT EXISTS questionResult (
         question VARCHAR(20), 
         exam VARCHAR(20),
         User VARCHAR(20), 
         answer VARCHAR(10000), 
         autograde VARCHAR(10), 
         adjustedGrade VARCHAR(10), 
         finalGrade VARCHAR(10),                                                              

         PRIMARY KEY (question, exam, user),
         FOREIGN KEY (exam) REFERENCES exam (name), 
         FOREIGN KEY (question) REFERENCES questions (name),
         FOREIGN KEY (user) REFERENCES user(name)                                       
); 
 
EOF
