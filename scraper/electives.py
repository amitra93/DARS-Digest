import MySQLdb

db = MySQLdb.connect(host="engr-cpanel-mysql.engr.illinois.edu", # your host, usually localhost
                     user="darsfordummies_x", # your username
                      passwd="XGonGive", # your password
                      db="darsfordummies_dars") # name of the data base
cur = db.cursor()
classes = [410, 411, 412, 413, 414, 418, 419, 420, 422, 423, 424, 425, 426, 427, 428, 429, 431, 433, 436, 438, 439, 440, 446, 450, 460, 461, 463, 465, 467, 475, 476, 477, 481, 498]
for i in classes:
	queryText = "INSERT INTO `darsfordummies_dars`.`requirements` (`major` ,`type` ,`courseList`) VALUES ('COMPUTER SCIENCE - COLLEGE OF ENGINEERING','TECHNICAL TRACK','CS"+str(i)+"')";
	print i
	try:
		cur.execute(queryText)
		db.commit()
	except:
	    db.rollback()
db.close()
