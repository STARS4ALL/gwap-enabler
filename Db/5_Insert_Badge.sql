/* 
 * (C) Copyright 2017 CEFRIEL (http://www.cefriel.com/).
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * Contributors:
 *     Andrea Fiano, Gloria Re Calegari, Irene Celino.
 */
 
LOCK TABLES `badge` WRITE;
/*!40000 ALTER TABLE `badge` DISABLE KEYS */;
INSERT INTO `badge` VALUES 
(1,'BABY','BABY_DESCRIPTION','images/icone-BADGE-BABY.png',1),
(2,'ADDICTED','ADDICTED_DESCRIPTION','images/icone-BADGE-ADDICTED.png',2),
(3,'EXPERT','EXPERT_DESCRIPTION','images/icone-BADGE-EXPERT.png',0),
(4,'GENIUS','GENIUS_DESCRIPTION','images/icone-BADGE-GENIUS.png',4),
(5,'DONKEY','DONKEY_DESCRIPTION','images/icone-BADGE-DONKEY.png',5),
(6,'LAZY','LAZY_DESCRIPTION','images/icone-BADGE-LAZY.png',6),
(7,'COLOSSAL','COLOSSAL_DESCRIPTION','images/icone-BADGE-COLOSSAL.png',7),
(8,'THE FLASH','THE_FLASH_DESCRIPTION','images/icone-BADGE-FLASH.png',8),
(9,'THE KING','THE_KING_DESCRIPTION','images/icone-BADGE-KING.png',9);
/*!40000 ALTER TABLE `badge` ENABLE KEYS */;
UNLOCK TABLES;
