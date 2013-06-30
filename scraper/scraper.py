#imports
import urllib2
import MySQLdb


#initialize variables
db = MySQLdb.connect(host="engr-cpanel-mysql.engr.illinois.edu", # your host, usually localhost
                     user="darsfordummies_x", # your username
                      passwd="XGonGive", # your password
                      db="darsfordummies_dars") # name of the data base
#AAS fully done. ABE done until 502
#AIS done until 430
#starting from RUSS
cur = db.cursor()
departmentList = (["AAS","ABE","ACCY","ACE","ACES","ADV","AE","AFAS","AFRO","AFST","AGCM","AGED","AHS",
				"AIS","ANSC","ANTH","ARAB","ARCH","ART","ARTD","ARTE","ARTF","ARTH","ARTS","ASST","ASTR",
				"ATMS","AVI","BADM","BASQ","BIOC","BIOE","BIOL","BIOP","BMNA","BTW","BULG","BUS","CAS",
				"CATL","CB","CDB","CEE","CHBE","CHEM","CHIN","CHLH","CHP","CI","CIC","CLCV","CMN","CPSC",
				"CS","CSE","CW","CWL","CZCH","DANC","EALC","ECE","ECON","EDPR","EDUC","EIL","ENG","ENGH",
				"ENGL","ENSU","ENT","ENVS","EOL","EPS","EPSY","ESE","ESL","EURO","FAA","FIN","FR","FSHN",
				"GC","GE","GEOG","GEOL","GER","GLBL","GMC","GRK","GRKM","GS","GWS","HCD","HDES","HDFS",
				"HEBR","HIST","HNDI","HORT","HRE","HUM","IB","IE","IHLT","INFO","ITAL","JAPN","JOUR",
				"JS","KIN","KOR","LA","LAS","LAST","LAT","LAW","LER","LGLA","LING","LIS","LLS","MACS",
				"MATH","MBA","MCB","MDIA","MDVL","ME","MICR","MILS","MIP","MSE","MSP","MUS","MUSE",
				"NEUR","NPRE","NRES","NS","NUTR","PATH","PBIO","PERS","PHIL","PHYS","PLPA","POL","PORT",
				"PS","PSM","PSYC","REES","REHB","RHET","RLST","RMLG","RSOC","RST","RUSS","SAME","SCAN",
				"SCR","SHS","SLAV","SLS","SNSK","SOC","SOCW","SPAN","SPED","STAT","SWAH","TAM","TE",
				"THEA","TMGT","TRST","TSM","TURK","UKR","UP","VCM","VM","WLOF","WRIT","YDSH","ZULU"])
baseURL = "https://courses.illinois.edu/cisapp/dispatcher/catalog/2013/fall/"
classList = [x for x in range(100,600)]
#name, title, courseCatalogLink, creditHours
for a in departmentList:
    for b in classList:
	if not db.open:
		db = MySQLdb.connect(host="engr-cpanel-mysql.engr.illinois.edu", # your host, usually localhost
                     user="darsfordummies_x", # your username
                      passwd="XGonGive", # your password
                      db="darsfordummies_dars") # name of the data base
        dbCourseCatalogLink = baseURL+str(a)+"/"+str(b)
        currentURL = urllib2.urlopen(dbCourseCatalogLink)
        dbName = str(a)+" "+str(b)
        currentURLText = currentURL.read()
        start = currentURLText.find('cis-section-course portlet-padtop1">')
        currentURLText = currentURLText[start+36:]
        end = currentURLText.find('</p>')
        if currentURLText[:end]=="":
            continue
        dbTitle = currentURLText[:end]
        print dbName
        print dbCourseCatalogLink
        print dbTitle
        start = currentURLText.find('<strong>Credit:</strong> ')
        currentURLText = currentURLText[start+25:]
        end = currentURLText.find('</p')
        currentURLText = currentURLText[:end]
        creditHours = [int(s) for s in currentURLText.split() if s.isdigit()]
	if creditHours==[]:
	    continue
	if len(creditHours)==1:
		dbCreditHours = creditHours[0]
        else:
		if creditHours[0]==0:
        		dbCreditHours = creditHours[1]
        	else:
			 dbCreditHours = creditHours[0]
        queryText = "INSERT INTO `darsfordummies_dars`.`class` (`name` ,`title` ,`courseCatalogLink` ,`creditHours`)VALUES ('"+str(dbName)+"', '"+str(dbTitle)+"', '"+str(dbCourseCatalogLink)+"', '"+str(dbCreditHours)+"');"
        print queryText
        try:
            cur.execute(queryText)
            db.commit()
        except:
	    db.rollback()

db.close()

