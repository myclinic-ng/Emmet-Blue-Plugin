<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Setup;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 18/04/2018 11:03 PM
 */
class InitDepartments
{
	public static $departments = [
		"Administration"=>[
			"IT/System Administration"=>[
				"roles"=>["In-house Support Staff", "Emmetblue Support"],
				"url"=>[
					"general"=>"human-resources/it/dashboard"
				]
			],
			"Human Resources"=>[
				"roles"=>["HR Officer"],
				"url"=>[
					"general"=>"human-resources/dashboard"
				]
			]

		],
		"Data Management"=>[
			"Health Information Unit"=>[
				"roles"=>["Front-desk Admin"],
				"url"=>[
					"general"=>"records/patient/dashboard"
				]
			],
			"Admissions/Discharge Unit"=>[
				"roles"=>["Admission Officer"],
				"url"=>[
					"general"=>"records/admissions-and-discharge/dashboard"
				]
			],
			"HMO Unit"=>[
				"roles"=>["Clerk"],
				"url"=>[
					"general"=>"accounts/hmo/dashboard"
				]
			]
		],
		"Drugs / Inventory"=>[
			"Dispensory"=>[
				"roles"=>["Pharmacist", "Sales Clerk"],
				"url"=>[
					"general"=>"pharmacy/dispensory/dashboard",
					"standalone"=>"pharmacy/standalone-dispensory/dashboard"
				]
			],
			"Pharmacy"=>[
				"roles"=>["Pharmacist", "Auditor"],
				"url"=>[
					"general"=>"pharmacy/dashboard"
				]
			]
		],
		"Financials"=>[
			"Financial Accounting"=>[
				"roles"=>["Accountant", "Auditor"],
				"url"=>[
					"general"=>"accounts/main/dashboard"
				]
			],
			"Billing"=>[
				"roles"=>["Cashier"],
				"url"=>[
					"general"=>"accounts/billing/dashboard"
				]
			],
			"Transaction Auditing"=>[
				"roles"=>["Auditor"],
				"url"=>[
					"general"=>"financial-audit/dashboard"
				]
			]
		],
		"Medicals"=>[
			"Medical Doctor/Consultation"=>[
				"roles"=>["Gynaecologist", "General Practitioner", "Dentist", "Opthalmologist", "Surgeon", "Psychiatrist", "Medical Director"],
				"url"=>[
					"general"=>"consultancy/dashboard"
				]
			],
			"In-Patient Nursing Unit"=>[
				"roles"=>["Nurse", "Matron"],
				"url"=>[
					"general"=>"nursing/ward/dashboard"
				]
			],
			"Out-Patient Nursing Unit"=>[
				"roles"=>["Nurse", "Matron"],
				"url"=>[
					"general"=>"nursing/station/dashboard"
				]
			],
			"OR/ER"=>[
				"roles"=>["Nurse", "Medical Doctor"],
				"url"=>[
					"general"=>"nursing/ward/dashboard"
				]
			]

		],
		"Laboratory"=>[
			"Combined Lab"=>[
				"roles"=>["Lab Scientist", "Lab Technician"],
				"url"=>[
					"general"=>"lab/dashboard"
				]
			],
			"Radiology"=>[
				"roles"=>["Radiologist", "Lab Technician"],
				"url"=>[
					"general"=>"lab/dashboard"
				]
			]
		]
	];

	public static $pharmacyModules = [
		"Administration"=>["IT/System Administration"=>[], "Human Resources"=>[]],
		"Drugs / Inventory"=>["Dispensory"=>[], "Pharmacy"=>[]],
		"Financials"=>["Billing"=>[]]
	];

	public static $labModules = [
		"Administration"=>["IT/System Administration"=>[]],
		"Laboratory"=>["Combined Lab"=>[], "Radiology"=>[]],
		"Financials"=>["Billing"=>[]]
	];

	public static function run(array $data = []){
		$preferredRole = $data["pref-role"] ?? "general";
		$profile = $data["profile"] ?? "hospital";

		if ($profile  == "pharmacy"){
			$modules = self::$pharmacyModules;
		}
		else if ($profile == "lab"){
			$modules = self::$labModules;
		}
		else {
			$modules = self::$departments;
		} 

		foreach ($modules as $group => $departments) {
			try {
				$groupId = \EmmetBlue\Plugins\HumanResources\DepartmentGroup\DepartmentGroup::create(["groupName"=>$group]);

				if (isset($groupId["lastInsertId"])){
					$groupId = $groupId["lastInsertId"];

					foreach ($departments as $name => $department){
						$res = self::runDepartment([
							"group"=>$group,
							"department"=>$name,
							"pref-role"=>$preferredRole,
							"groupId"=>$groupId
						]);
					}
				}
			}
			catch(\Exception $e){
			}
		}

		return true;
	}

	public static function runDepartment(array $data){
		$group = $data["group"];
		$name = $data["department"];
		$preferredRole = $data["pref-role"] ?? "general";

		if (isset(self::$departments[$group][$name])){
			if (!isset($data["groupId"])){
				$query = "SELECT DepartmentGroupID FROM Staffs.DepartmentGroup WHERE GroupName = '$group'";
				$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
				$groupId = $result[0]["DepartmentGroupID"];
			}
			else {
				$groupId = $data["groupId"];
			}

			$deptId = \EmmetBlue\Plugins\HumanResources\Department\Department::create(["groupId"=>$groupId, "name"=>$name]);

			if (isset($deptId["lastInsertId"])){
				$deptId = $deptId["lastInsertId"];
				$department = self::$departments[$group][$name];

				$url = $department["url"][$preferredRole] ?? $department["url"]["general"];
				\EmmetBlue\Plugins\HumanResources\Department\Department::newRootUrl(["department"=>$deptId, "url"=>$url]);

				$roles = $department["roles"];

				foreach ($roles as $role){
					\EmmetBlue\Plugins\HumanResources\Role\Role::create(["name"=>$role, "department"=>$deptId]);
				}

				return true;
			}
		}

		return false;
	}

}