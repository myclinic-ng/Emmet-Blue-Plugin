<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
/**
 * class SavedDiagnosis Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class SavedDiagnosis
{
   public static function newSavedDiagnosis(array $data){
        $patient = $data["patient"];
        $consultant = $data["consultant"];
        $diagnosis = $data["diagnosis"];

        $diagnosis = serialize($diagnosis);
        DBConnectionFactory::getConnection()->exec("DELETE FROM Consultancy.SavedDiagnosis WHERE Consultant = $consultant AND Patient = $patient");
        
        $query = "INSERT INTO Consultancy.SavedDiagnosis (Patient, Consultant, Diagnosis) VALUES ($patient, $consultant, '".$diagnosis."')";

        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
   }

   public static function viewAllSavedDiagnosis(int $patient){
      $query = "SELECT Consultant, DateModified, Patient, SavedDiagnosisID FROM Consultancy.SavedDiagnosis WHERE Patient = ".$patient;

      $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

      foreach ($result as $key => $value) {
          $result[$key]["ConsultantDetail"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["Consultant"]);
          $result[$key]["ConsultantDetail"]["Role"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $value["Consultant"])["Name"];
      }

      return $result;    
   }

   public static function viewSavedDiagnosis(int $consultant, array $data){
        $query = "SELECT * FROM Consultancy.SavedDiagnosis WHERE Consultant = $consultant AND Patient = ".$data['patient'];

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
            $result[$key]["Diagnosis"] = unserialize($value["Diagnosis"]);
        }

        return $result;
   }

   public static function viewPatients(int $consultant){
        $query = "select a.SavedDiagnosisID, b.* from Consultancy.SavedDiagnosis a INNER JOIN Patients.Patient b ON a.Patient = b.PatientID WHERE a.Consultant = $consultant";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
   }

   public static function deleteSavedDiagnosis(int $diagnosisId){
      return DBConnectionFactory::getConnection()->exec("DELETE FROM Consultancy.SavedDiagnosis WHERE SavedDiagnosisID = $diagnosisId");
   }
}