<?php
namespace Indeed\Ihc\Db;

class ProrateMembershipGroups
{
    /**
     * @var string
     */
    private static $optionName          = 'ihc_prorate_membership_groups';

    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
     * @param int
     * @return boolean
     */
    public static function create( $attr=[] )
    {
        if ( empty( $attr ) || empty( $attr['memberships'] ) ){
            return false;
        }
        $groups = get_option( self::$optionName, [] );
        if ( $groups === false || count( $groups ) === 0 ){
            $groups[1] = [
                            'memberships'       => $attr['memberships'],
                            'name'              => $attr['name'],
            ];
        } else {
            $groups[] = [
                            'memberships'       => $attr['memberships'],
                            'name'              => $attr['name'],
            ];
        }

        return update_option( self::$optionName, $groups );
    }

    /**
     * @param int
     * @return boolean
     */
    public static function update( $attr=[] )
    {
        $groups = get_option( self::$optionName, false );
        $groupId = isset( $attr['id'] ) ? $attr['id'] : false;
        if ( $groupId === false ){
            return false;
        }
        if ( $groups === false ){
            return false;
        }
        if ( isset( $groups[ $groupId ] ) && isset( $attr['memberships'] ) ){
            foreach ( $attr['memberships'] as $id => $level ){
                $levelGroup = self::getGroupForLid( $level );
                if ( $levelGroup !== false && (int)$levelGroup !== (int)$groupId ){
                    unset( $attr['memberships'][$id] );
                }
            }
            $groups[ $groupId ]['memberships'] = $attr['memberships'];
            if ( isset( $attr['name'] ) ){
                $groups[ $groupId ]['name'] = $attr['name'];
            }
            return update_option( self::$optionName, $groups );
        }
        return false;
    }

    /**
     * @param int
     * @return boolean
     */
    public static function deleteOne( $groupId=0 )
    {
        if ( $groupId === 0 ){
            return false;
        }
        $groups = get_option( self::$optionName, false );
        if ( $groups === false ){
            return false;
        }
        if ( isset( $groups[ $groupId ] ) ){
            unset( $groups[ $groupId ] );
        }
        return update_option( self::$optionName, $groups );
    }

    /**
     * @param int
     * @return mixed ( array or boolean )
     */
    public static function getOne( $groupId=0 )
    {
        if ( $groupId === false ){
            return false;
        }
        $groups = get_option( self::$optionName, false );
        if ( $groups === false ){
            return false;
        }
        if ( isset( $groups[ $groupId ] ) ){
            return $groups[ $groupId ];
        }
        return false;
    }

    /**
     * @param int
     * @return mixed ( int or boolean )
     */
    public static function getGroupForLid( $lid=0 )
    {
        if ( $lid === 0 ){
            return false;
        }
        $groups = get_option( self::$optionName, false );
        if ( $groups === false || count( $groups ) === 0 ){
            return false;
        }
        foreach ( $groups as $groupId => $groupData ){
            if ( empty( $groupData ) || empty( $groupData['memberships'] ) ){
                continue;
            }
            foreach ( $groupData['memberships'] as $levelId ){
                if ( (int)$levelId === (int)$lid ){
                    return $groupId;
                }
            }
        }
        return false;
    }


    /**
     * @param none
     * @return array
     */
    public static function getAll()
    {
        $groups = get_option( self::$optionName, [] );
        return $groups;
    }

    /**
     * @param none
     * @return array
     */
    public static function getAllMembershipsWithGroup()
    {
        $groups = get_option( self::$optionName, false );
        if ( $groups === false || count( $groups ) === 0 ){
            return [];
        }
        $array = [];
        foreach ( $groups as $groupId => $groupData ){
            if ( !isset( $groupData['memberships'] ) ){
                continue;
            }
            foreach ( $groupData['memberships'] as $levelId ){
                $array[] = $levelId;
            }
        }
        return $array;
    }


}
