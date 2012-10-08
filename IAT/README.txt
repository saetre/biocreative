#How to link to the IAT data-folder
[biocreative]$ ln -s /home/a/17/busstuc/public_html/biocreative_data/ data


Get correct abstract:

http://www-tsujii.is.s.u-tokyo.ac.jp/medie/showxml.cgi?position=medline09n0433-39584026-medline09n0433-39586594&sentence_id=s1

Find out which one medie is using:
15024	/works/mlesna3a/medie-admin/data-2009/medline09n0691/medline09n0691	16840	18753
15024	/works/mlesna3a/medie-admin/data-2009/medline09n0436/medline09n0436	9275383	9277292
(Check out how to post (ajax?) to Medie from PHP /medie/medie.js putResults? ...)

Remember where the species info comes from
merged-species-ascii -> /home/zonen/smp/data/BioCreative3/IAT/merged

Remember where the gene normalisation info comes from:
GNSuite.txt -> /home/wijs/okazaki/research/bc3iat/bc3iat-20100818.txt
