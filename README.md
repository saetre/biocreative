biocreative_IAT
===============

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

####################################################################

#When push is rejected (because of server side updates), do

[satre@furu ~/public_html/biocreative]$ git remote update

Updating origin
From git@github.com:saetre/biocreative
   f434deb..b866269  master     -> origin/master

AND

[satre@furu ~/public_html/biocreative]$ git merge origin/master

IAT/merged-species-ascii_link: needs update
IAT/results_link: needs update
Merge made by recursive.
 IAT/README.txt |    2 +-
 1 files changed, 1 insertions(+), 1 deletions(-)

AND

[satre@furu ~/public_html/biocreative]$ git push origin master

Counting objects: 21, done.
Compressing objects: 100% (13/13), done.
Writing objects: 100% (15/15), 1.82 KiB, done.
Total 15 (delta 7), reused 0 (delta 0)
To git@github.com:saetre/biocreative.git
   b866269..ded1d9c  master -> master
