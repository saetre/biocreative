biocreative_IAT
===============
#Small test here :-)

BioCreative III Interactive Annotation Task

#In order to be able to sync changes
git remote add origin git@github.com:saetre/biocreative.git

#Check if the config looks ok
cat .git/config


#How to merge (local master) and push changes back to GitHub (origin/master)
# (after "git remote update"  #updates from remote GitHub)

# fetch and merge from (remote) origin/master to local master (HEAD)

git pull git@github.com:saetre/biocreative.git HEAD 

#Then add new files
git add *
git commit
git push origin master

 # (or "git merge origin/master" )