<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy\Infermedica;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;

/**
 * class Infermedica Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class Infermedica
{
	private static $apiURL = "https://api.infermedica.com/v2/";

    public static function searchSymptoms(array $data){
        $dirts = [
            "(", ")", "-"
        ];
        foreach ($dirts as $dirt){
            $data["phrase"] = str_replace($dirt, " ", $data["phrase"]);
        }
    	$phrase = $data["phrase"];
    	$size = $data['size'] ?? 10000;
    	$from = $data['from'] ?? 0;

    	$query = explode(" ", $phrase);
        $builtQuery = [];
        foreach ($query as $element){
            $builtQuery[] = "(".$element."* ".$element."~)";
        }

        $builtQuery = implode(" AND ", $builtQuery);
        
        $params = [
            'index'=>'infermedica',
            'type'=>'symptoms',
            'size'=>$size,
            'from'=>$from,
            'body'=>array(
                "query"=>array(
                    "query_string"=>array(
                        "query"=>$builtQuery
                    )
                )
            )
        ];

        $esClient = ESClientFactory::getClient();

        return $esClient->search($params);
   }

   public static function symptoms(array $data){
   		try {
            
            $esClient = ESClientFactory::getClient();

            $params = [
                'index'=>'infermedica',
                'type' =>'symptoms',
                'id'=>$data["id"]
            ];

            return $esClient->get($params);
        }
        catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e){
        	return [];
        }
   }

   public static function labTests(array $data){
   		$result = HTTPRequestFactory::get(self::$apiURL."lab_tests/".$data["id"]);

   		return json_decode($result->body, true);
   }
}