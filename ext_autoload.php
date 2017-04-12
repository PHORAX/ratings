<?php
$extensionPath = t3lib_extMgm::extPath('ratings');


return array(
	'tx_ratings_api' => $extensionPath . 'class.tx_ratings_api.php',
	'tx_ratings_pi1' => $extensionPath . 'pi1/class.tx_ratings_pi1.php',
);

?>