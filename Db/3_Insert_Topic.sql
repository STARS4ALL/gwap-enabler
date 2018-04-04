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

LOCK TABLES `topic` WRITE;
/*!40000 ALTER TABLE `topic` DISABLE KEYS */;
INSERT INTO `topic` VALUES 
(1,NULL,'black','BLACK_DESCRIPTION',NULL,0.016),
(2,NULL,'city','CITY_DESCRIPTION',NULL,0.719),
(3,NULL,'stars','STARS_DESCRIPTION',NULL,0.021),
(4,NULL,'aurora','AURORA_DESCRIPTION',NULL,0.147),
(5,NULL,'astronaut','ISS_DESCRIPTION',NULL,0.006),
(6,NULL,'none','NONE_DESCRIPTION',NULL,0.091),
(7,NULL,'404','NO_PHOTO_DESCRIPTION',NULL,NULL),
(8,NULL,'unknown','I_DONT_KNOW_DESCRIPTION',NULL,NULL);
/*!40000 ALTER TABLE `topic` ENABLE KEYS */;
UNLOCK TABLES;
