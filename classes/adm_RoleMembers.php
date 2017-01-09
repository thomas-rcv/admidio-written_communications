<?php
/******************************************************************************/
/** @class RoleMembers
 *  @brief This class reads members of roles.
 *
 *  This class is developed, to read active,former or all members of a selected role from database.
 *  Also it can read all users from database and search users using a search term for lastname or firstname.
 *  The result of the database query is stored in an associative array with needed information
 *  for each member. Each recordset contains User ID, name, address, location, postcode, birthday and 
 *  status of role membership and also leader status of the role. All role memberships
 *  of the member are also counted and stored in the output array. 
 *  Additional profile fields can be set with function to expand the user datas of the recordset if required 
 *  @par Returned array with recordsets:
 *  @code 
 *  array(         
 *    [0] => Array
 *          (
 *              [0] => 6
 *              [usr_id] => 6
 *              [1] => Lastname
 *              [last_name] => Lastname
 *              [2] => Firstname
 *              [first_name] => Firstname
 *              [3] => 
 *              [city] => 
 *              [4] => 
 *              [address] => 
 *              [5] => 
 *              [zip_code] => 
 *              [6] => DEU
 *              [country] => DEU
 *              [7] => 6
 *              [member_this_role] => 6
 *              [8] => 0
 *              [leader_this_role] => 0
 *              [9] => 1
 *              [member_this_orga] => 1
 *          )
 *  )
 *  @endcode
 */
 /******************************************************************************
 *
 *  Copyright    : (c) 2004 - 2017 The Admidio Team
 *  Homepage     : http://www.admidio.org
 *  Author:      : Thomas-RCV
 *  License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *******************************************************************************/

class RoleMembers
{
    public  $arrMembers;                ///< Array with prepared recordset of all members of the current role    
    public  $db;                        ///< Database object to handle the communication with the database. This must be public because of session handling.
    private $limit;                     ///< Limit of the query result
    private $memberCondition;           ///< Internal member condition
    private $roleId;                    ///< Requested role ID
    private $showMembers;               ///< Paramter to handle member status of the query ( default "0" for active members of the role )
    private $sqlConditions;             ///< Internal SQL condition statement for the query
    protected $additionalProfileFields; ///< String with additional profile fields added to the database query
    protected $additionalSQLJoin;       ///< String for additional table join to get profile field data
    
    /**
     *  Constructor that will create an object of a recordset of the specified role.
     *  Optional parameter for status of a membership can be set to chosse all, active or former memberships of the current role
     *  @param $db Object of the class database. This should be the default object @b $gDb. 
     *  @param $rol_id Id of the role the members are needed
     *  @param $showMembers Status for the query( Default 0:  for active members, 1 for former members, 2 all member )
     *  @par Example
     *  @code // Creating the instance 
     *  $members = new RoleMembers($gDB, 2) // Role Id 2 is set. Also a third parameter can be passed for the status of membership (0 = active, 1 = former, 2 = all)  
     *  $arrResult = $members->getRoleMembers();
     *  @endcode
     */
    public function __construct(&$db, $rol_id = 0, $showMembers = 0)
    {
        global $gCurrentOrganization;
        
        $this->additionalProfileFields  = '';
        $this->additionalSQLJoin        = '';
        $this->arrMembers               = array();
        $this->db                       =& $db;
        $this->limit                    = '';
        $this->roleId                   = $rol_id;
        $this->showMembers              = $showMembers; 
        
        if($this->showMembers == 1)
        {
            // only former members
            $this->sqlConditions = ' AND mem_end < \''.DATE_NOW.'\' ';
        }
        elseif($this->showMembers == 2)
        {
            // former members and active members
            $this->sqlConditions = ' AND mem_begin < \''.DATE_NOW.'\' ';
        }
        else
        {
            // only active members
            $this->sqlConditions = ' AND mem_begin  <= \''.DATE_NOW.'\'
                               AND mem_end     > \''.DATE_NOW.'\' ';
        }
        
        $this->memberCondition  = ' EXISTS
                                    (SELECT 1
                                       FROM '. TBL_MEMBERS. ', '. TBL_ROLES. ', '. TBL_CATEGORIES. '
                                      WHERE mem_usr_id = usr_id
                                        AND mem_rol_id = rol_id '. $this->sqlConditions .'
                                        AND rol_valid  = 1
                                        AND rol_cat_id = cat_id
                                        AND (  cat_org_id = '. $gCurrentOrganization->getValue('org_id'). '
                                            OR cat_org_id IS NULL )) ';
    }
    
    /**
     *  Add additional profile fields to SQL query 
     *  @param $array Array with key/value pairs. The value must be like the profile field named in data_field_intern
     */
    public function addProfileFields($array)
    {
        global $gProfileFields;
        
        if(!is_array($array))
        {
            throw new Exception('Profile fields must be passed as array with key=>value to add profile fields');
        }
        foreach($array as $key => $value)
        {
            $this->additionalProfileFields .= $value.'.usd_value as '.$value.',';
            $this->additionalSQLJoin .= 'LEFT JOIN '. TBL_USER_DATA. ' as '.$value.'
                                            ON '.$value.'.usd_usr_id = usr_id
                                            AND '.$value.'.usd_usf_id = '. $gProfileFields->getProperty(strtoupper($value), 'usf_id') .' ';
        }
    }
    
    /**
     *  Prepare SQL query an get the recordset as array
     *  @return Returns an associative array with recordsets of all members of the current role. 
     */
    public function getRoleMembers()
    {
        global $gCurrentOrganization;
        global $gProfileFields;

         // Prepare SQL statement
        $sql = 'SELECT usr_id, last_name.usd_value as last_name, first_name.usd_value as first_name, birthday.usd_value as birthday,
                        city.usd_value as city, address.usd_value as address, zip_code.usd_value as zip_code, country.usd_value as country,
                        '.$this->additionalProfileFields.' mem_usr_id as member_this_role, mem_leader as leader_this_role,
                          (SELECT count(*)
                             FROM '. TBL_ROLES. ' rol2, '. TBL_CATEGORIES. ' cat2, '. TBL_MEMBERS. ' mem2
                            WHERE rol2.rol_valid   = 1
                              AND rol2.rol_cat_id  = cat2.cat_id
                              AND (  cat2.cat_org_id = '. $gCurrentOrganization->getValue('org_id'). '
                                  OR cat2.cat_org_id IS NULL )
                              AND mem2.mem_rol_id  = rol2.rol_id
                              AND mem2.mem_begin  <= \''.DATE_NOW.'\'
                              AND mem2.mem_end     > \''.DATE_NOW.'\'
                              AND mem2.mem_usr_id  = usr_id) as member_this_orga
                FROM '. TBL_USERS. '
                LEFT JOIN '. TBL_USER_DATA. ' as last_name
                  ON last_name.usd_usr_id = usr_id
                 AND last_name.usd_usf_id = '. $gProfileFields->getProperty('LAST_NAME', 'usf_id'). '
                LEFT JOIN '. TBL_USER_DATA. ' as first_name
                  ON first_name.usd_usr_id = usr_id
                 AND first_name.usd_usf_id = '. $gProfileFields->getProperty('FIRST_NAME', 'usf_id'). '
                LEFT JOIN '. TBL_USER_DATA. ' as birthday
                  ON birthday.usd_usr_id = usr_id
                 AND birthday.usd_usf_id = '. $gProfileFields->getProperty('BIRTHDAY', 'usf_id'). '
                LEFT JOIN '. TBL_USER_DATA. ' as city
                  ON city.usd_usr_id = usr_id
                 AND city.usd_usf_id = '. $gProfileFields->getProperty('CITY', 'usf_id'). '
                LEFT JOIN '. TBL_USER_DATA. ' as address
                  ON address.usd_usr_id = usr_id
                 AND address.usd_usf_id = '. $gProfileFields->getProperty('ADDRESS', 'usf_id'). '
                LEFT JOIN '. TBL_USER_DATA. ' as zip_code
                  ON zip_code.usd_usr_id = usr_id
                 AND zip_code.usd_usf_id = '. $gProfileFields->getProperty('POSTCODE', 'usf_id'). '
                LEFT JOIN '. TBL_USER_DATA. ' as country
                  ON country.usd_usr_id = usr_id
                 AND country.usd_usf_id = '. $gProfileFields->getProperty('COUNTRY', 'usf_id').' 
                    
                '.$this->additionalSQLJoin.' 
                 
                LEFT JOIN '. TBL_ROLES. ' rol
                  ON rol.rol_valid   = 1
                 AND rol.rol_id      = '.$this->roleId.'
                LEFT JOIN '. TBL_MEMBERS. ' mem
                  ON mem.mem_rol_id  = rol.rol_id '. $this->sqlConditions .'
                 AND mem.mem_usr_id  = usr_id      
                WHERE '. $this->memberCondition. '
                ORDER BY last_name, first_name '.$this->limit;
        
        $resultUser = $this->db->query($sql);
        while($row = $resultUser->fetch())
        {
            // Read role members of the current role
            if($row['member_this_role'] > 0)
            {  
                $this->arrMembers[] = $row;
            }
        }
        return $this->arrMembers;
    }
    
    /**
     *  Set a limit for the database query
     *  @param $integer Integer value as limit 
     */
    public function setLimit($integer)
    {
        if(is_numeric($integer))
        {
            $this->limit = ' LIMIT '.$integer;
        }
        return $this;
    }
    
    /**
     *  Set a search term for members
     *  @param $term Searchstring for members looking for lastname and firstname
     */
    public function setSearchTerm($term)
    {

	    foreach($term as $search_therm)
	    {
	    	$this->memberCondition .= ' AND (  (UPPER(last_name.usd_value)  LIKE UPPER(\''.$search_therm.'%\'))
									       OR (UPPER(first_name.usd_value) LIKE UPPER(\''.$search_therm.'%\'))) ';
	    }
	    return $this;
    }
    
    /**
     *  Get all members from database 
     */
    public function showAllMembers()
    {
        $this->memberCondition = ' usr_valid = 1 ';
        $this->roleId = '2';
        return $this->getRoleMembers(); 
    }
}
?>