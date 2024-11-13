<?php
/**
 * Simply Schedule Appointments Resource Object Factory.
 *
 * @since   0.0.3
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Resource Object Factory.
 *
 * @since 0.0.3
 */
class SSA_Resource_Group_Object_Factory extends SSA_Resource_Object {

    public static function create( array $data = array() ) {
        static $id = 0;
        $id++;

        $instance = new SSA_Resource_Group_Object( $id );
        $fixture_data = array (
            'id' => $id,
            'title' => 'Resource Group A',
            'description' => 'Resource Description',
            'resource_type' => 'identical',
            'quantity' => '1',
            'status' => 'publish',
        );

        $data = array_merge( $fixture_data, $data );
        
        if(isset($data['resources'])) {
            $resources = $data['resources'];
            unset( $data['resources'] );
            $instance->resources = $resources;
        }

        $instance->data = $data;

        return $instance;
    }

    public static function create_random( int $number_of_items = 0 , array $data = array() ) {
        $title = self::generate_title();
        $title = sanitize_title( $title );
        $fixture_data = array (
            'title' => $title,
            'quantity' => $number_of_items
        );
        $data = array_merge( $fixture_data, $data );
        if($data['resource_type'] === 'identifiable') {
            $resources = SSA_Resource_Object_Factory::create_random($number_of_items);
            $data['resources'] = $resources;
        }
        $instance = self::create( $data);

        return $instance;
    }

    public static function generate_title() {
        $choices = array(
            'Resource Group A',
            'Resource Group B',
            'Resource Group C',
            'Resource Group D',
            'Resource Group E',
        );
        return $choices[array_rand( $choices )];
    }
}
