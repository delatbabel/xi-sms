<?php

/**
 * This file is part of the Xi SMS package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Sms\Gateway;

use Xi\Sms\SmsMessage;

/**
 * BulkSMS.com SMS Gateway
 *
 * This interface implements GatewayInterface and provides an interface
 * to the BulkSMS SMS gateway.
 *
 * Pricing varies around 3 euro cents per message, free trial account with
 * 5 free credits available. 
 *
 * @reference http://www.bulksms.com/int/ web site.
 * @reference http://www.bulksms.com/int/w/products-apis.htm SMS APIs
 * @reference http://www.bulksms.com/int/w/eapi-sms-gateway.htm API details for the HTTP to SMS API (EAPI)
 * @reference http://www.bulksms.com/int/w/pricing.htm SMS pricing
 * @reference http://bulksms.vsms.net/register/ Registration page
 * @reference http://www.bulksms.com/int/docs/eapi/code_samples/php/ PHP code samples
 * @reference http://www.bulksms.com/int/docs/eapi/code_samples/php/sendsms/ Code sample
 */
class BulkSmsGateway extends BaseHttpRequestGateway
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var integer
     */
    protected $port;

    /**
     * @var integer
     */
    protected $bits;

    /**
     * Transient error codes.
     *
     * These error codes will be retried if encountered. For your final application,
     * you may wish to include statuses such as "25: You do not have sufficient credits"
     * in this list, and notify yourself upon such errors. However, if you are writing a
     * simple application which does no queueing (e.g. a Web page where a user simply
     * POSTs a form back to the page that will send the message), then you should not
     * add anything to this list (perhaps even removing the item below), and rather just
     * display an error to your user immediately.
     *
     * @var array
     */
    protected $transient_errors = array(
        40 => 1 # Temporarily unavailable
    );

    /**
     * 7 bit test message.
     *
     * A 7-bit GSM SMS message can contain up to 160 characters (longer messages can be
     * achieved using concatenation).
     *
     * All non-alphanumeric 7-bit GSM characters are included in this example. Note that Greek characters,
     * and extended GSM characters (e.g. the caret "^"), may not be supported
     * to all networks. Please let us know if you require support for any characters that
     * do not appear to work to your network.
     *
     * @var string
     */
    protected $seven_bit_test_msg = "Test message: all non-alphanumeric GSM characters: $@!\"#%&,;:<>Â¡Â£Â¤Â¥Â§Â¿Ã„Ã…Ã†Ã‡Ã‰Ã‘Ã–Ã˜ÃœÃŸÃ Ã¨Ã©Ã¹Ã¬Ã²Ã¥Â¿Ã¤Ã¶Ã±Ã¼Ã \nGreek: Î©Î˜Î”Î¦Î“Î›Î©Î Î¨Î£Î˜Îž";
    
    /**
     * 16 bit unicode test message.
     *
     * A Unicode SMS can contain only 70 characters. Any Unicode character can be sent,
     * including those GSM and accented ISO-8859 European characters that are not
     * catered for by the GSM character set, but note that mobile phones are only able
     * to display certain Unicode characters, based on their geographic market.
     * Nonetheless, every mobile phone should be able to display at least the text
     * "Unicode test message:" from the test message below. Your browser may be able to
     * display more of the characters than your phone. The text of the message below is:
     *
     * @var string
     */
    protected $unicode_test_msg = "Unicode test message: â˜º \nArabic: Ø´ØµØ¶\nChinese: æœ¬ç½‘";

    /**
     * Constructor.
     *
     * We recommend that you use port 5567 instead of port 80, but your
     * firewall will probably block access to this port (see FAQ for more
     * details).
     *
     * @param string $username
     * @param string $password
     * @param string $endpoint defaults to http://bulksms.vsms.net/eapi/submission/send_sms/2/2.0
     * @param integer $port should be 80 or 5567
     * @param integer $bits should be 7 or 16 (unicode)
     */
     */
    public function __construct(
        $username,
        $password,
        $endpoint = 'http://bulksms.vsms.net/eapi/submission/send_sms/2/2.0',
        $port = 80,
        $bits = 7,
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->endpoint = $endpoint;
        $this->port = $port;
        $this->bits = $bits;
    }

    /**
     * Send a message.
     *
     * @see GatewayInterface::send
     * @todo Implement a smarter method of sending (batch)
     */
    public function send(SmsMessage $message)
    {
        $body = urlencode(utf8_decode($message->getBody()));
        $from = urlencode($message->getFrom());
        $url = $this->endpoint;

        foreach ($message->getTo() as $to) {

            switch($this->bits) {
                case 7:
                    //
                    // Sending 7-bit message
                    //
                    $post_body = $this->seven_bit_sms($body, $to);
                    $this->getClient()->post($url, array(), $post_body);
                break;
                
                case 16:
                    //
                    // Sending unicode message
                    //
                    $post_body = $this->unicode_sms($body, $to);
                    $this->getClient()->post($url, array(), $post_body);
                break;
            }
        }
        return true;
    }

    /**
     * Make the post body for a 7 bit SMS message.
     *
     * @param string $message
     * @param string $msisdn
     * @return string
     */
    protected function seven_bit_sms($message, $msisdn) {
        $post_fields = array (
            'username' => $this->username,
            'password' => $this->password,
            'message'  => $this->character_resolve( $message ),
            'msisdn'   => $msisdn
        );

        return $this->make_post_body($post_fields);
    }

    /**
     * Make the post body for a unicode SMS message.
     *
     * @param string $username
     * @param string $password
     * @param string $message
     * @param string $msisdn
     * @return string
     */
    protected function unicode_sms($message, $msisdn) {
        $post_fields = array (
            'username' => $this->username,
            'password' => $this->password,
            'message'  => $this->string_to_utf16_hex($message),
            'msisdn'   => $msisdn,
            'dca'      => '16bit'
        );

        return make_post_body($post_fields);
    }

    /**
     * Resolve 7 bit characters
     *
     * @param string $body
     * @return string
     */
    protected function character_resolve($body) {
        $special_chrs = array(
            'Î”'=>'0xD0', 'Î¦'=>'0xDE', 'Î“'=>'0xAC', 'Î›'=>'0xC2', 'Î©'=>'0xDB',
            'Î '=>'0xBA', 'Î¨'=>'0xDD', 'Î£'=>'0xCA', 'Î˜'=>'0xD4', 'Îž'=>'0xB1',
            'Â¡'=>'0xA1', 'Â£'=>'0xA3', 'Â¤'=>'0xA4', 'Â¥'=>'0xA5', 'Â§'=>'0xA7',
            'Â¿'=>'0xBF', 'Ã„'=>'0xC4', 'Ã…'=>'0xC5', 'Ã†'=>'0xC6', 'Ã‡'=>'0xC7',
            'Ã‰'=>'0xC9', 'Ã‘'=>'0xD1', 'Ã–'=>'0xD6', 'Ã˜'=>'0xD8', 'Ãœ'=>'0xDC',
            'ÃŸ'=>'0xDF', 'Ã '=>'0xE0', 'Ã¤'=>'0xE4', 'Ã¥'=>'0xE5', 'Ã¦'=>'0xE6',
            'Ã¨'=>'0xE8', 'Ã©'=>'0xE9', 'Ã¬'=>'0xEC', 'Ã±'=>'0xF1', 'Ã²'=>'0xF2',
            'Ã¶'=>'0xF6', 'Ã¸'=>'0xF8', 'Ã¹'=>'0xF9', 'Ã¼'=>'0xFC',
        );

        $ret_msg = '';
        if( mb_detect_encoding($body, 'UTF-8') != 'UTF-8' ) {
                        $body = utf8_encode($body);
                }
                for ( $i = 0; $i < mb_strlen( $body, 'UTF-8' ); $i++ ) {
                        $c = mb_substr( $body, $i, 1, 'UTF-8' );
                        if( isset( $special_chrs[ $c ] ) ) {
                                $ret_msg .= chr( $special_chrs[ $c ] );
                        }
                        else {
                                $ret_msg .= $c;
                        }
                }
        return $ret_msg;
    }

    /**
     * Convert unicode characters
     *
     * @param string $body
     * @return string
     */
    protected function string_to_utf16_hex( $string ) {
        return bin2hex(mb_convert_encoding($string, "UTF-16", "UTF-8"));
    }

    /**
     * Make a generic post body for curl.
     *
     * @param array $post_fields
     * @return string
     */
    protected function make_post_body($post_fields) {
        $stop_dup_id = $this->make_stop_dup_id();
        if ($stop_dup_id > 0) {
            $post_fields['stop_dup_id'] = $this->make_stop_dup_id();
        }
        $post_body = '';
        foreach( $post_fields as $key => $value ) {
            $post_body .= urlencode( $key ) . '=' . urlencode( $value ) . '&';
        }
        $post_body = rtrim( $post_body,'&' );

        return $post_body;
    }
    
    /**
     * Make a unique ID (optional)
     *    
     * Unique ID to eliminate duplicates in case of network timeouts - see
     * EAPI documentation for more. You may want to use a database primary
     * key. Warning: sending two different messages with the same
     * ID will result in the second being ignored!
     *
     * Don't use a timestamp - for instance, your application may be able
     * to generate multiple messages with the same ID within a second, or
     * part thereof.
     *
     * You can't simply use an incrementing counter, if there's a chance that
     * the counter will be reset.
     *
     * @return integer
     */
    protected function make_stop_dup_id() {
        return 0;
    }

}
