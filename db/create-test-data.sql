-- Create 3 Users {'jblow','bsmith','janedoe'} with password 'change.me'
REPLACE INTO `user_account` (`id`,`firstName`,`lastName`,`enabled`,`sysadmin`,`sysuser`,
`initials`,`middleName`,`email`,`organization`,`phone`,`poc`,`pocPhone`,`notes`)
VALUES
('jblow','Joe','Blow',1,1,1,'JB','Lance','joeblow@email.com','The Organization','999-555-1212','POC Guy','999-111-2222','Some notes'),
('bsmith','Bill','Smith',1,1,1,'BS','Smitty','billsmith@email.com','Test Organization','999-555-1313','POC Guy','999-111-2233',NULL),
('janedoe','Jane','Doe',1,1,1,'JD','Jenny','janedoe@email.com','Test Organization','999-555-1313','POC Girl','999-111-2233',NULL);

REPLACE INTO `user_credentials` (`id`,`password`) VALUES
('jblow','$2y$10$c5OnMdM4h0zTp0WkwbnLMuMLftM9IKGqcVjkgUzuz.KQznQ89S8pu'),
('bsmith','$2y$10$c5OnMdM4h0zTp0WkwbnLMuMLftM9IKGqcVjkgUzuz.KQznQ89S8pu'),
('janedoe','$2y$10$c5OnMdM4h0zTp0WkwbnLMuMLftM9IKGqcVjkgUzuz.KQznQ89S8pu');

-- Create Forums
REPLACE INTO `forum` (`id`,`name`,`owner`,`description`,`creationDate`)
VALUES
('f1','First Forum','jblow','This is where the description of the first forum should be.  There should be enough information here for someone who is not part of the forum to understand what the context is.','2014-11-11 20:32:51'),
('f2','Second Forum','janedoe','This is where the description of the second forum should be.  There should be enough information here for someone who is not part of the forum to understand what the context is.','2014-12-11 20:32:51'),
('f3','Third Forum','janedoe','Description for third forum','2015-01-11 20:32:51'),
('f4','Fourth Forum','jblow','This is where the description of the fourth forum should be.  There should be enough information here for someone who is not part of the forum to understand what the context is.','2015-02-11 20:32:51'),
('f5','Fifth Forum','janedoe','This is where the description of the fifth forum should be.  There should be enough information here for someone who is not part of the forum to understand what the context is.','2015-03-11 20:32:51'),
('flintstones','The Flintstones','jblow','Information about \"The Flintstones\" cartoon television series','2015-04-11 20:32:51');

/*
  Create forum file nodes
  Note that root nodes are never displayed only their contents.
*/
DELETE FROM `forum_file_node` WHERE `forumId` IN ('f1','f2','f3','f4','f5','flintstones'); 

INSERT INTO `forum_file_node` (`id`,`forumId`,`parentId`,`name`,`contentType`)
VALUES
-- Root nodes
('f1','f1',NULL,'f1 root','#folder'),
('f2','f2',NULL,'f2 root','#folder'),
('f3','f3',NULL,'f3 root','#folder'),
('f4','f4',NULL,'f4 root','#folder'),
('f5','f5',NULL,'f5 root','#folder'),
('flintstones','flintstones',NULL,'flintstones root','#folder'),

-- Create a Documents folder in each root
('f1.docs','f1','f1','Documents','#folder'),
('f2.docs','f2','f2','Documents','#folder'),
('f3.docs','f3','f3','Documents','#folder'),
('f4.docs','f4','f4','Documents','#folder'),
('f5.docs','f5','f5','Documents','#folder'),
('flinstones.docs','flintstones','flintstones','Documents','#folder'),
-- Create a hierarchy in the first forum
('f1.level.0','f1','f1','Test Levels','#folder'),
('f1.level.1','f1','f1.level.0','Level 1','#folder'),
('f1.level.2','f1','f1.level.1','Level 2','#folder'),
('f1.level.3','f1','f1.level.2','Level 3','#folder'),
('f1.level.4','f1','f1.level.3','Level 4','#folder'),
('f1.level.5','f1','f1.level.4','Level 5','#folder'),
('f1.level.6','f1','f1.level.5','Level 6','#folder'),
('f1.level.7','f1','f1.level.6','Level 7','#folder'),
('f1.level.8','f1','f1.level.7','Level 8','#folder'),
('f1.level.9','f1','f1.level.8','Level 9','#folder'),
-- Create a list of folders in one folder
('f1.list','f1','f1','Folder List','#folder'),
('f1.list.0','f1','f1.list','Folder 0','#folder'),
('f1.list.1','f1','f1.list','Folder 1','#folder'),
('f1.list.2','f1','f1.list','Folder 2','#folder'),
('f1.list.3','f1','f1.list','Folder 3','#folder'),
('f1.list.4','f1','f1.list','Folder 4','#folder'),
('f1.list.5','f1','f1.list','Folder 5','#folder'),
('f1.list.6','f1','f1.list','Folder 6','#folder'),
('f1.list.7','f1','f1.list','Folder 7','#folder'),
('f1.list.8','f1','f1.list','Folder 8','#folder'),
('f1.list.9','f1','f1.list','Folder 9','#folder');

/*
  Remove all forum enrollment data for the test users and then create the 
  following enrollment scenario:
   jblow JOINED  f1
   jblow INVITED f2 by janedoe
   jblow PENDING JOIN f3
   jblow JOINED f4
   jblow REJECTED f5 by janedoe
   jblow JOINED flintstones

   janedoe LEFT  f1
   janedoe JOINED f2 
   janedoe JOINED f3
   janedoe INVITED f4 by jblow
   janedoe DECLINED INVITE f5
   janedoe JOINED flintstones
   
   bsmith SUSPENDED flintstones by janedoe
*/
DELETE FROM `forum_user` WHERE `userId` IN ('jblow','janedoe','bsmith'); 
INSERT INTO `forum_user` (`forumId`,`userId`,`enrollmentStatus`,`lastUpdated`,`updateUserId`)
VALUES
('f1','jblow','J','2015-04-01 18:45:24','jblow'),
('f2','jblow','I','2015-04-01 18:45:24','janedoe'),
('f3','jblow','P','2015-04-01 18:45:24','jblow'),
('f4','jblow','J','2015-04-01 18:45:24','jblow'),
('f5','jblow','R','2015-04-01 18:45:24','janedoe'),
('flintstones','jblow','J','2015-04-01 18:45:24','jblow'),
('f1','janedoe','L','2015-04-01 18:45:24','janedoe'),
('f2','janedoe','J','2015-04-01 18:45:24','janedoe'),
('f3','janedoe','J','2015-04-01 18:45:24','janedoe'),
('f4','janedoe','I','2015-04-01 18:45:24','jblow'),
('f5','janedoe','D','2015-04-01 18:45:24','janedoe'),
('flintstones','janedoe','J','2015-04-01 18:45:24','janedoe'),
('flintstones','bsmith','S','2015-04-01 18:45:24','janedoe');

-- Create posts in the Flintstones forum
DELETE FROM `forum_post` WHERE `forumId` = 'flintstones'; 
INSERT INTO `forum_post` (`forumId`,`userId`,`postDate`,`title`,`postStatus`,
`postType`,`parentId`,`content`,`contentType`)
VALUES
('flintstones','mgrippaldi','2015-02-25 21:23:42','Fred Flintstone','publish','post',0,'<div><p style=\"text-align: center;\"></p><div style=\"text-align: left;\"><img src=\"http://upload.wikimedia.org/wikipedia/en/a/ad/Fred_Flintstone.png\" style=\"width: 25%;float: left;\"/><span>While the mid-1980s spin-off series The Flintstone Kids depicts Fred as a child, the series may be apocryphal due to its presenting Wilma as a childhood friend of Fred and Barney; the original series asserts that they first met as young adults. Still, the series\' depictions that Fred is the only child of Ed and Edna Flintstone (a handyman and a homemaker respectively) might be taken as canon.</span></div><p></p></div><div><span>As young adults, Fred and Barney worked as bellhops at a resort. There, they meet and fall in love with Wilma and Betty, who were working there as cigarette girls. Wilma\'s mother, Pearl Slaghoople, also met her future son-in-law, and took an instant disliking toward Fred (and vice versa), starting a long-lasting rivalry between the two.[12] An unspecified amount of time later, Fred married Wilma (and Barney married Betty).</span><br/></div><div><br/></div><div>Fred is a typical blue-collar worker, who works as a &#34;bronto crane operator&#34; at Slate Rock and Gravel Company (also known as Rockhead and Quarry Cave Construction Company in the earliest episodes). Fred\'s job title in the second season episode &#34;Divided We Sail&#34; is geological engineer.[13]</div><div><br/></div><div>During the original series\' third season, Wilma gives birth to the couple\'s daughter, Pebbles. Years later, when Pebbles is a teenager, Fred and Barney join the Bedrock police force for a time as part-time police officers.[1] Eventually, Fred becomes a grandfather to the adult Pebbles and Bamm-Bamm\'s offspring, twins Chip and Roxy. Fred\'s family grew again in A Flintstone Family Christmas, when he and Wilma adopted an orphaned caveboy named Stony, and despite a rough start, Fred and his new son bonded well.</div><div></div>','text/html'),
('flintstones','mgrippaldi','2015-02-25 19:24:58','Barney Rubble','publish','post',0,'<p><img src=\"http://upload.wikimedia.org/wikipedia/en/e/e2/Barney_Rubble.png\" style=\"width: 25%;float: left;\"/>While the mid-1980s spinoff series The Flintstone Kids depicts Barney as a child, the series seems to be mostly apocryphal due to its presenting Barney as a childhood friend of Wilma and Betty (versus the original series\' assertion that they first met as young adults). Still, the series\' assertions that Barney has at least one younger brother, Dusty, was a childhood friend of Fred, and was the son of artist Flo Rubble and car dealer Robert &#34;Honest Bob&#34; Rubble might be taken as valid. It is suggested in the original series that Barney grew up at 142 Boulder Avenue in Granitetown. The original series also suggested in one episode that Barney was the nephew of Fred\'s boss, Mr. Slate, though subsequent episodes and spinoffs don\'t seem to support this claim.[7] As young adults, Barney and Fred worked as bellhops at a resort, where they first met Wilma and Betty, who were working as cigarette girls.[8] Eventually, Barney married Betty (as Fred did Wilma).</p><p><span>Several episodes and spinoffs suggest that Barney, along with Fred, spent some time in the army early in their marriages, though said references may be to Barney and Fred\'s military service in the first season episode &#34;The Astr\'nuts.&#34;</span></p><p><span>While the subject of Barney\'s occupation (or even if he had one) was never given during the original series, the majority of subsequent spinoffs suggest at some point after the original series, Barney went to work at the Slate Rock and Gravel Company quarry alongside Fred as a fellow dino-crane operator. An early episode of the original series does have a brief scene of Barney working at the Granite Building.[9] When speaking to an upper-crust snob in another episode, Betty declares Barney is in &#34;top-secret&#34; work; but that might have been a cover for a low-level job or unemployment, or perhaps an in-joke meaning that Barney\'s job was unknown even to the show\'s writers. It could also be possible that both Fred and Barney work at the quarry, but may work in different sections of it, under different bosses. In one episode, Barney\'s boss tells him to &#34;put down his broom,&#34; which implies some sort of janitorial work is involved.</span></p><p><span>During the fourth season of the original series, Betty and Barney found an abandoned infant on their doorstep, by the name of &#34;Bamm-Bamm.&#34; A court battle ensued between the couple and a wealthy man who also had wanted to adopt Bamm-Bamm. Barney and Betty were successful in their efforts to adopt Bamm-Bamm because the wealthy man gave up (after winning the case) upon learning his wife became pregnant, after which he became a staple character on the series.[10] For a number of episodes after Bamm-Bamm\'s debut, there is no sign of him on the show. In the fifth season, the family buys a pet hopparoo (a combination of a kangaroo and dinosaur) named Hoppy.</span></p>','text/html'),
('flintstones','mgrippaldi','2015-02-25 21:21:22','Wilma Flintstone','publish','post',0,'<p><img src=\"https://upload.wikimedia.org/wikipedia/en/9/97/Wilma_Flintstone.png\" style=\"float: left;width: 25%;\"/>While the mid-1980s spin-off series The Flintstone Kids depicts Wilma as a child, the series seems to be mostly apocryphal due to its presenting Wilma as a childhood friend of Fred and Barney (the original series asserted that they first met as young adults[8]). Still, the series\' depictions that Wilma had younger sisters (twins named Mickey and Mica) a older Brother Jerry Slaghoople (Mentioned in the movie)and that her fatherâ€”who apparently died by the time Wilma reached adulthood[9]â€”ran a prehistoric computer business might be taken as canon. Wilma mentions having a married sister in the sixth season of the original series.</p><p><span>As a young adult, Wilma worked with Betty as cigarette girls/waitresses at a resort. There, they first met and fell in love with their future husbands, Fred and Barney (who were working there as bellhops). Wilma\'s mother, Pearl Slaghoople, also met her future son-in-law, and took a disliking toward Fred (and vice versa), starting a long-lasting rivalry between the two.[8]</span><br/></p><p><span>Eventually, Wilma and Fred were married, and Wilma became a homemaker, keeping house with such prehistoric aids as a baby elephant vacuum cleaner, pelican washing machine, and so forth. Wilma is also a good cook; one of her specialties is &#34;gravelberry pie,&#34; the recipe for which she eventually sold to the &#34;Safestone&#34; supermarket chain.[11] Wilma also enjoys volunteering for various charitable and women\'s organizations in Bedrock, shopping, and (occasionally) getting to meet the celebrities of their world, including Stony Curtis,[12] Rock Quarry,[13] and Jimmy Darrock.[14]</span><br/></p><p><span>In the original series\' third season, Wilma becomes pregnant, and gives birth to the couple\'s only child, Pebbles.</span></p><p>When Pebbles becomes a teenager, Wilma (along with Betty) gains employment as a reporter for one of Bedrock\'s newspapers, the Daily Granite (a spoof of the Daily Planet of Superman fame), under editor Lou Granite (a parody of The Mary Tyler Moore Show\'s Lou Grant). While employed there, Wilma shares various adventures with prehistoric superhero Captain Caveman, who (in a secret identity) also works for the newspaper (again a spoof of Superman).[1]</p>','text/html');

