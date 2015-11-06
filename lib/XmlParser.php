<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 10.09.2015
 * Time: 17:47
 */

namespace yii\multiparser;


class XmlParser extends  Parser{

    public $node;

    public function read()
    {
        $file = $this->file;
        $result = $this->xmlToArray( $file->getPathname() );

        if ( isset($this->node) ) {

            $result = $result[ $this->node ];

        }

        $this->cleanUp();
        return $result;
    }


    /**
     * Converts an XML string to a PHP array
     * @param $file_path
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    protected function xmlToArray( $file_path ) {

        try {
            $xml = new \SimpleXMLElement( $file_path, 0, true );
            //\common\components\CustomVarDamp::dumpAndDie($xml->children()->children());
            $result = $this->recursiveXMLToArray( $xml );
        } catch(Exception $ex) {

            throw $ex;
        }

        return $result;
    }

    /**
     * Convert a XML string to a PHP array recursively. Do not
     * call this function directly
     *
     * @param SimpleXMLElement
     *
     * @return mixed
     */
    protected  function recursiveXMLToArray($xml) {
        if( $xml instanceof \SimpleXMLElement ) {
            $attributes = $xml->attributes();

            foreach( $attributes as $key => $value ) {
                if( $value ) {
                    $attribute_array[$key] = (string) $value;
                }
            }
            $previous_xml = $xml;
            $xml = get_object_vars($xml);
        }

        if(is_array($xml)) {

            if( count($xml) == 0 )
                return (string) $previous_xml; // for CDATA

            foreach($xml as $key => $value) {
                $row[$key] = $this->recursiveXMLToArray($value);
            }
            if ( is_string($value) ) {
                // дошли до конца рекурсии
                // преобразуем ряд согласно конфигурации
                if ( $this->keys !== NULL ) {
                    // назначим ключи из конфигурации, согласно массиву $keys
                    $row = $this->compareArrayWithKeys( $row );
                }
                $row = $this->convert( $row );

            }


            if( isset( $attribute_array ) )
                $row['@'] = $attribute_array; // Attributes

            return $row;
        }
        return (string) $xml;
    }

    /**
     * @param array $value_arr - текущий ряд, массив, которому нужно назначить конфигурационные ключи ($keys)
     * @return array
     */
    protected function compareArrayWithKeys( array $value_arr ){
        $res = $this->keys;
        foreach ( $this->keys as $key => $value ) {
            if ( array_key_exists( $value, $value_arr ) ) {
                $res[$key] = $value_arr[$value];
            }
        }
        return $res;
    }

}