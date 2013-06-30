#imports
import MySQLdb

#initialize variables
db = MySQLdb.connect(host="engr-cpanel-mysql.engr.illinois.edu", # your host, usually localhost
                     user="darsfordummies_x", # your username
                      passwd="XGonGive", # your password
                      db="darsfordummies_dars") # name of the data base
cur = db.cursor()
query = "SELECT DISTINCT crossListedAs from class"
cur.execute(query)
data = cur.fetchall()
classList = []
for row in data:
        row = str(row)
        classList.append(row[2:len(row)-3])
print "Class array finalized. Searching for crosslisted courses"
baseURL = "https://courses.illinois.edu/cisapp/dispatcher/catalog/2013/fall/"
##name, title, courseCatalogLink, creditHours, alsoKnownAs
dbCourseCatalogLink = baseURL+"AAS"+"/"+"100"
file = open('out.txt', 'w')
for key,i in enumerate(classList):
        print>>file, i
		
