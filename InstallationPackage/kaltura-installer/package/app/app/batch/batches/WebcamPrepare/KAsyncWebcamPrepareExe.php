<?php
/**
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Copyright 2014 Akvelon Inc.
 * http://www.akvelon.com/contact-us
 */

/**
 * Executes the KWebcamPrepare
 *
 * @package Scheduler
 * @subpackage WebcamPrepare
 */
require_once("bootstrap.php");

$instance = new KAsyncWebcamPrepare();
$instance->run();
$instance->done();