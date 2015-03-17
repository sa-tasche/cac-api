<?php
/**
 * cac-api - PHP wrapper for the cloudatcost.com API
 *
 * @author      Fabio Di Sotto <fabio.disotto@gmail.com>
 * @copyright   2015 Fabio Di Sotto
 * @link        https://github.com/fdisotto/cac-api
 * @version     1.0.0
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace fdisotto;

/**
 * CACApi
 *
 * Wrapper class for the cloudatcost.com API
 *
 * @author Fabio Di Sotto
 */
class CACApi
{
    /**
     * @var array
     */
    private $_data = array();

    /**
     * @var array
     */
    private $_response = null;

    /**
     * @var \Curl\Curl
     */
    private $_curl = null;

    /**
     * @const string
     */
    const BASE_URL = 'https://panel.cloudatcost.com/api/';

    /**
     * @const string
     */
    const API_VERSION = 'v1';

    /**
     * @const string
     */
    const SERVERS_URL = '/listservers.php';

    /**
     * @const string
     */
    const TEMPLATES_URL = '/listtemplates.php';

    /**
     * @const string
     */
    const TASKS_URL = '/listtasks.php';

    /**
     * @const string
     */
    const POWER_OP_URL = '/powerop.php';

    /**
     * @const string
     */
    const CONSOLE_URL = '/console.php';

    /**
     * Constructor
     *
     * @param array $conf key and login prameters
     */
    public function __construct(array $conf)
    {
        if (!in_array('curl', get_loaded_extensions())) {
            throw new \Exception('cURL is not enabled', 0);
        }

        $this->_curl = new \Curl\Curl();

        $this->_data = $conf;
    }

    /**
     * Return an array containing server details.
     * https://github.com/cloudatcost/api#list-servers
     *
     * @return array Server details
     */
    public function getServers()
    {
        $this->_make_request(self::SERVERS_URL, $this->_data);
        return $this->_response['data'];
    }

    /**
     * Return an array containing template information.
     * https://github.com/cloudatcost/api#list-templates
     *
     * @return array Template information
     */
    public function getTemplates()
    {
        $this->_make_request(self::TEMPLATES_URL, $this->_data);
        return $this->_response['data'];
    }

    /**
     * Return an array containing task information.
     * https://github.com/cloudatcost/api#list-tasks
     *
     * @return array Task information
     */
    public function getTasks()
    {
        $this->_make_request(self::TASKS_URL, $this->_data);
        return $this->_response['data'];
    }

    /**
     * Request that the server specified be powered on.
     * https://github.com/cloudatcost/api#power-operations
     *
     * @param int $sid Server ID
     * @return bool
     */
    public function powerOnServer($sid = '')
    {
        return $this->_make_power_operation($sid, 'poweron');
    }

    /**
     * Request that the server specified be powered off.
     * https://github.com/cloudatcost/api#power-operations
     *
     * @param int $sid Server ID
     * @return bool
     */
    public function powerOffServer($sid = '')
    {
        return $this->_make_power_operation($sid, 'poweroff');
    }

    /**
     * Request that the server specified be power cycled.
     * https://github.com/cloudatcost/api#power-operations
     *
     * @param int $sid Server ID
     * @return bool
     */
    public function resetServer($sid = '')
    {
        return $this->_make_power_operation($sid, 'reset');
    }

    /**
     * Return the URL to the web console for the server specified.
     * https://github.com/cloudatcost/api#power-operations
     *
     * @param int $sid Server ID
     * @return string Console url
     */
    public function getConsoleUrl($sid = '')
    {
        $data = $this->_data;
        $data['sid'] = $sid;
        $this->_make_request(self::CONSOLE_URL, $data, 'POST');

        return $this->_response['console'];
    }

    /**
     * Perform the web request to the C@C API
     *
     * @param string $where The endpont of the url
     * @param array $data Options for the web request
     * @param string $type GET or POST web request
     * @return bool
     */
    private function _make_request($where, $data, $type = 'GET')
    {
        if ($type == 'GET') {
            try {
                $this->_curl->get(self::BASE_URL . self::API_VERSION . $where, $data);
                $this->_response = json_decode($this->_curl->response, true);
            } catch (\Exception $e) {
                throw new \Exception('Something gone wrong', 0, $e);
            }
        }
        elseif ($type == 'POST') {
            try {
                $this->_curl->post(self::BASE_URL . self::API_VERSION . $where, $data);
                $this->_response = json_decode($this->_curl->response, true);
            } catch (\Exception $e) {
                throw new \Exception('Something gone wrong', 0, $e);
            }
        } else {
            throw new \Exception('Invalid Request Type', 0);
        }

        return true;
    }

    /**
     * Perform the power operation on the server
     *
     * @param int $sid Server ID
     * @param string $action poweron, poweroff, reset request
     * @return bool
     */
    private function _make_power_operation($sid, $action)
    {
        $data = $this->_data;

        $data['sid'] = $sid;
        $data['action'] = $action;

        try {
            $this->_make_request(self::POWER_OP_URL, $data, 'POST');
        } catch (\Exception $e) {
            throw new \Exception('Something gone wrong', 0, $e);
        }

        return true;
    }
}