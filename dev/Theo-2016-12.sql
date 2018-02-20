# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.6.25)
# Database: HealthyRecipes
# Generation Time: 2016-12-23 10:37:25 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table Recipe
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Recipe`;

CREATE TABLE `Recipe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `name` tinytext,
  `recipeDescription` mediumtext,
  `userID` int(11) DEFAULT NULL,
  `mainImageID` int(11) DEFAULT NULL,
  `method` text,
  `serves` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `Recipe` WRITE;
/*!40000 ALTER TABLE `Recipe` DISABLE KEYS */;

INSERT INTO `Recipe` (`id`, `created`, `name`, `recipeDescription`, `userID`, `mainImageID`, `method`, `serves`)
VALUES
	(5,NULL,'Flan Sponge',NULL,1,NULL,'Cream butter and sugar together until the mixture is light and fluffy. Whisk eggs lightly and add gradually to creamed mixture beating inbetween each addition.\n\nFold in flour quickly and lightly. \n\nTurn misture into well greased (8 inch - 20 cm) sponge flan tin.\n\nBake in preheated mod oven for about 20 minutes or until well risen and golden.\n\nAllow to cool in tin until it shrinks from sides and then turn out on wire rack.',4),
	(6,NULL,'Curry Puffs',NULL,NULL,NULL,'Heat oil and lightly brown mince. Add onion and curry powder and flour and cook for 2 minutes. \nAdd apple and banana and fry one minute longer. Add chutney, bay leaf, water and seasoning. Stir well bring to simmer and cook gently for 15-20 minutes until meat is tender. Allow to become cold.\n\nNote: If you want to make this into one pie rather than  packages, change puff pastry to short crust  and grease pie dish - place a bottom layer of pastry into dish so that it lines dish and sides, then put in the meat mixture and top with pastry lid. Cut vent hole and brush top with egg and milk mixture - bake in hot oven -200C for 30-40 minutes.\n\nNote if you want to make a slightly smaller pie cut mince down to 500grams but leave all the other ingredients the same.\n\n\n\nTo make packages - thaw puff pastry and cut each sheet into four squares. Place a tablespoon of the mixture in the centre of each square. Brush edges with egg and milk or water and bring the corners together in the centre, sealing well.\nBrush with egg and milk ad bake in a very hot oven 230C 450F for 20-25 minutes.\n\nDipping sauce for packages - combine 1 carton yoghurt with 2 tablespoons fresh chipped mint or very finely chopped peeled cucumber.',20),
	(7,NULL,'Meatballs','',NULL,NULL,'In a large bowl, whisk egg with mustard, salt, pepper and thyme.Crumble in ground beef and form into balls.Cook meatballs in frying pan over medium low heat, turning often, until balls are golden all around.Put aside for use in recipes. Can be used fresh or freeze in appropriate container.',NULL),
	(8,NULL,'Porridge Oats Cookies','A wholesome and delicious tor a quick breakfast or for a healthy snack anytime!',NULL,NULL,'Preheat oven to 350¡F (180¡C).Combine whole wheat flour, baking soda, salt. allspice, cinnamon and ginger; stir well and set aside.Place eggs, vegetable oil, unsweetened applesauce and brown sugar in a large bowl and beat together well. Add orange rind, vanilla, porridge oats, chopped almonds and sunßower seeds and stir until well combined. Add ßour mixture and stir well. Add dried cranberries and stir until well distributed.Using about 1/4 cup (50 ml) of cookie dough for each cookie, place on prepared cookie sheet and press down with a fork to form a cookie approximately 3.5\" (9 cm) in diameter. Bake for 15 - 20 minutes or until lightly browned. Let cool on the cookie sheet before removing.\"',NULL),
	(9,NULL,'Southern Fried Chicken','Total Time: 24 minPrep: 10 minCook: 14 minYield: 4 servingsLevel: Easy',NULL,NULL,'In a medium size bowl, beat the eggs with the water. Add enough hot sauce so the egg mixture is bright orange. In another bowl, combine the flour and pepper. Season the chicken with the house seasoning. Dip the seasoned chicken in the egg, and then coat well in the flour mixture.Heat the oil to 350 degrees F in a deep pot. Do not fill the pot more than 1/2 full with oil.Fry the chicken in the oil until brown and crisp. Dark meat takes longer then white meat. It should take dark meat about 13 to 14 minutes, white meat around 8 to 10 minutes.House Seasoning:Mix ingredients together and store in an airtight container for up to 6 months.',NULL);

/*!40000 ALTER TABLE `Recipe` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
