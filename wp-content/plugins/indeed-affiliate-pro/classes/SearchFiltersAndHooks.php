<?php
namespace Indeed\Uap;
/*
in order to view all hooks and filters you can go to : http://{yourdomain}/wp-admin/admin.php?page=ihc_manage&tab=hooks
*/
class SearchFiltersAndHooks
{
    /**
     * @var array
     */
    private $files                    = [];
    /**
     * @var array
     */
    private $hooks                    = [];
    /**
     * @var string
     */
    private $pluginName               = '';
    /**
     * @var array
     */
    private $nameShouldContain        = [];

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {

    }

    /**
     * @param string
     * @return object
     */
    public function setNameShouldContain( $input='' )
    {
        if ( is_array( $input ) ){
            $this->nameShouldContain = $input;
        } else {
            $this->nameShouldContain[] = $input;
        }
        return $this;
    }

    /**
     * @param string
     * @return object
     */
    public function setPluginName( $input='' )
    {
        $this->pluginName = $input;
        return $this;
    }

    /**
     * @param string
     * @return none
     */
    public function SearchFiles( $path='' )
    {
        if ( file_exists($path) && is_dir($path) ){
            $result = scandir($path);
            $files = array_diff($result, array('.', '..'));
            if ( count( $files ) > 0){
                foreach ( $files as $file ){
                    if ( is_file( "$path/$file" ) ){
                        $this->files[] = "$path/$file";
                    } else if( is_dir( "$path/$file" ) ){
                        $this->SearchFiles( "$path/$file" );
                    }
                }
            }
        }
    }

    /**
     * @param none
     * @return object
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param none
     * @return array
     */
    public function getResults()
    {
        if ( !$this->files ){
            return [];
        }
        foreach ( $this->files as $filePath ){
            $this->searchIntoFile( $filePath );
        }
        return $this->hooks;
    }

    /**
     * @param string
     * @return none
     */
    public function searchIntoFile( $file='' )
    {
        $contents = file_get_contents( $file );
        $pluginFileName = $file;
        if ( $this->pluginName ){
            $pluginFileName = explode( $this->pluginName, $file );
            $pluginFileName = isset( $pluginFileName[1] ) ? $pluginFileName[1] : $file;
            $pluginFileName = str_replace( '//', '/', $pluginFileName );
        }

        $this->searchForHooksIntoString( $contents, 'filter', $file, $pluginFileName );
        $this->searchForHooksIntoString( $contents, 'action', $file, $pluginFileName );

        unset( $contents );
    }

    /**
     * @param string
     * @param string
     * @param object
     * @param string
     * @return object
     */
    private function searchForHooksIntoString( &$string='', $type='', $file=null, $pluginFileName='' )
    {
        if ( $type == 'filter' ){
            $search = 'apply_filters(';
        } else {
            $search = 'do_action(';
        }

        $pattern = preg_quote( $search, '/' );
        $pattern = "/^.*$pattern.*\$/m";
        if ( preg_match_all( $pattern, $string, $matches ) ){
            foreach ( $matches[0] as $match ){
                $temporary = explode( $search, $match );
                if ( empty( $temporary[1] ) ){
                    continue;
                }
                $temporary[1] = trim( $temporary[1] );
                if ( stripos( $temporary[1], "'" ) === 0 ){
                    $temporaryName = explode( "'", $temporary[1] );
                } else {
                    $temporaryName = explode( '"', $temporary[1] );
                }
                $name = isset( $temporaryName[1] ) ? $temporaryName[1] : '';
                if ( !$this->checkName( $name ) ){
                    continue;
                }
                $description = $this->getDescription( $file, $match );
                if ( isset( $this->hooks[$name] ) ){
                    $this->hooks[$name]['file'][] = $pluginFileName;
                } else {
                    $this->hooks[$name] = [
                              'file'              => [ $pluginFileName ],
                              'description'       => $description,
                              'type'              => $type,
                    ];
                }
            }
        }
    }

    /**
     * @param string
     * @return bool
     */
    private function checkName( $name='' )
    {
        if ( !$this->nameShouldContain ){
            return true;
        }
        foreach ( $this->nameShouldContain as $nameSubstring ){
            if ( stripos( $name, $nameSubstring ) !== false ){
                return true;
            }
        }
        return false;
    }

    /**
     * @param string
     * @param string
     * @return string
     */
    private function getDescription( $fileName = '', $subString='' )
    {
        $lines = file( $fileName );
        $line = -1;
        foreach ( $lines as $lineNumber => $line ) {
            if ( strpos( $line, $subString ) !== false ) {
                $line = $lineNumber;
                break;
            }
        }
        if ( $line > -1 ){
            $line++;
            $data = $lines[$line];
            $data = trim($data);
            if ( stripos( $data, '// @description' ) === 0 ){
                $data = str_replace( '// @description', '', $data );
                return $data;
            }
        }
        return '';
    }
    
}
