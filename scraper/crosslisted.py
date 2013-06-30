#imports
import urllib2
import MySQLdb


#initialize variables
db = MySQLdb.connect(host="engr-cpanel-mysql.engr.illinois.edu", # your host, usually localhost
                     user="darsfordummies_x", # your username
                      passwd="XGonGive", # your password
                      db="darsfordummies_dars") # name of the data base
cur = db.cursor()
query = "SELECT name from class"
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
length = len(classList)
for key,i in enumerate(classList):
        department = i[:i.index(" ")]
        number = i[i.index(" ")+1:]
        if department<"ENGL":
                continue
        dbCourseCatalogLink = baseURL+department+"/"+number
        currentURLText = urllib2.urlopen(dbCourseCatalogLink).read()
        if " See " not in currentURLText:
                print i+", not a crosslisted class."
        else:
                currentURLText = currentURLText[currentURLText.index(" See ")+5:]
                currentURLText = currentURLText[:currentURLText.index(".")]
                if len(currentURLText)>8:
                        continue
                print i+", also known as "+currentURLText+"."
                if not db.open:
                        db = MySQLdb.connect(host="engr-cpanel-mysql.engr.illinois.edu",user="darsfordummies_x",passwd="XGonGive",db="darsfordummies_dars")
                cur = db.cursor()
                query = "UPDATE class SET crossListedAs='"+currentURLText+"' WHERE name='"+i+"'";
                cur.execute(query)
                db.commit()
