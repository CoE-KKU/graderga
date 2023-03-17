import mysql.connector
import time
import urllib3
from os import path
from Garedami.Src import Judge
import requests

try:
    dbconnector = mysql.connector.connect(
        host='localhost',
	port='3306',
        user='p0ndja',
        password='P0ndJ@1103',
        database='grader.ga'
    )
except mysql.connector.Error:
    print("WTF WHY I CAN'T CONNECT TO DATABASE")
    exit(0)

def getTimeAndMem(idTask):
    mycursor = dbconnector.cursor(buffered=True)
    mycursor.execute(f"SELECT `time`,`memory` FROM `problem` WHERE `id` = {idTask} LIMIT 1")
    result = mycursor.fetchall()
    if (len(result)):
        return result[0][0],result[0][1]
    return -69,-420

def getWaitSubmission():
    try:
        mycursor = dbconnector.cursor(buffered=True)
        mycursor.execute("SELECT `id`,`user`,`problem`,`lang`,`script` FROM `submission` WHERE `result` = 'W' ORDER BY `id`") #Get specific data from submission SQL where result is W (Wait)
        return mycursor.fetchall()
    except Exception as e:
        print("[!] ERROR losing connection to database:\n", e)
        print("[!] The system will be halt for 30 seconds and will try again.")
        time.sleep(60)
        return getWaitSubmission()

if __name__ == '__main__':
    try:
        mycursor = dbconnector.cursor(buffered=True)
    except mysql.connector.Error:
        print("WTF WHY I CAN'T CONNECT TO DATABASE")
        exit(0)
    webLocation = "/" + path.join("var","www","grader.ga")

    print("Grader.py started")

    while(1):
        queue = getWaitSubmission()
        if (len(queue)):
            print("Founded Waiting Queue : ",len(queue))
            print(queue)
        for myresult in queue:
            #Get data from query
            subID = myresult[0] #id is the 1st.
            userID = myresult[1] #user is the 2nd.
            probID = myresult[2] #problem is the 3rd.
            lang = myresult[3] #lang is the 4th.
            userCodeLocation = myresult[4].replace("..",webLocation) #script location is the 5th.
            #userCodeLocation in format "../file/judge/upload/<User ID>/<Codename>-<EPOCH>.<lang>", real location need change "../" to webLocation
            #Full path: /var/www/grader.ga/file/judge/upload/<User ID>/<Codename>-<EPOCH>.<lang>

            print(f"----------<OwO>----------\nJudging: submission={subID}, problem={probID}, user={userID}")

            probTestcaseLocation = path.join(webLocation,"file","judge","prob",str(probID))
            #print(probTestcaseLocation)
            #All testcases will be here

            srcCode = ""

            with open(userCodeLocation,"r") as f:
                srcCode = f.read()

            probTime,probMem = getTimeAndMem(probID)

            if probTime < 0:
                judgeResult = ("WebError",0,100,0,0,"Web API Down")
            else:
                judgeResult = Judge.judge(probID,lang,probTestcaseLocation,srcCode,probTime,probMem)
            #Result from judge
            result = judgeResult[0]
            score = int(judgeResult[1])
            maxScore = int(judgeResult[2])
            runningTime = int(judgeResult[3]) #ms
            memory = int(judgeResult[4]) #MB
            comment = judgeResult[5]

            #Update to SQL
            query = ("UPDATE `submission` SET `result` = %s,`score` = %s,`maxScore` = %s,`runningTime` = %s,`memory` = %s,`comment` = %s WHERE `id` = %s")
            data = (result, score, maxScore, runningTime, memory, comment, subID) #Don't forget to add subID
            mycursor.execute(query, data)
            print(f"Finished Judge submission={subID}, problem={probID}, user={userID} --> {result}")

            #Make sure that query is done.
            dbconnector.commit()
            time.sleep(1)
        dbconnector.commit()
        #Time sleep interval for 10 second.
        try:
            time.sleep(10)	
        except KeyboardInterrupt:
            print("Bye bye")
            exit(0)
