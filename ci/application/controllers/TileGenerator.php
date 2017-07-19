<?hh

set_include_path(get_include_path().PATH_SEPARATOR.'../osm_parser');
set_include_path(
  get_include_path().PATH_SEPARATOR.'../osm_bounding_box_calculator',
);
set_include_path(
  get_include_path().PATH_SEPARATOR.'../osm_img_generator',
);

spl_autoload_extensions('.php');

spl_autoload_register(
  function($class_name) {
    include $class_name.'.php';
  },
);

class TileGenerator extends CI_Controller {

		public function __construct (): void {
			parent::__construct();
			$this->load->database();
		}

		public async function getTileByLocation(): Awaitable<void> {
			// the post data is at $_POST, or $this->input->post(), or $this->input->raw_input_stream
			header('Access-Control-Allow-Origin: *'); // allow cross site call

			$get = $_GET;
			$at = idx($get, 'at');
			if ($at === null) {
				$this->output->set_content_type('application/json');
	      $theHTMLResponse = <window>inside window, request_uri: {$_SERVER['REQUEST_URI']}</window>;
	      $this->output->set_output(json_encode(Map {
					'error_code' => 1,
					'err_msg'=> 'need lat,long,level',
				}));
				return;
			}

			$image = await OsmTileGenerator::create()->genTileByLocation($at);
			if ($image === null) {
				$this->output->set_content_type('application/json');
	      $this->output->set_output(json_encode(Map {
					'error_code' => 1,
					'err_msg'=> 'failed to generate image',
				}));
				return;
			}

			// $image->writeImage('/tmp/test.png');
			// $this->output->set_content_type('image/'.$image->getImageFormat());
			// $this->output->set_output(file_get_contents('/tmp/test.png'));
			$this->output->set_content_type('image/'.$image->getImageFormat());
			$this->output->set_output($image->getImageBlob());
		}

    public async function getTile(): Awaitable<void> {
			// the post data is at $_POST, or $this->input->post(), or $this->input->raw_input_stream
			header('Access-Control-Allow-Origin: *'); // allow cross site call

			$get = $_GET;
			$at = idx($get, 'at');
			if ($at === null) {
				$this->output->set_content_type('application/json');
	      $theHTMLResponse = <window>inside window, request_uri: {$_SERVER['REQUEST_URI']}</window>;
	      $this->output->set_output(json_encode(Map {
					'error_code' => 1,
					'err_msg'=> 'need lat,long,level',
				}));
				return;
			}

			$image = await OsmTileGenerator::create()->genTile($at);
			if ($image === null) {
				$this->output->set_content_type('application/json');
	      $this->output->set_output(json_encode(Map {
					'error_code' => 1,
					'err_msg'=> 'failed to generate image',
				}));
				return;
			}

			// $image->writeImage('/tmp/test.png');
			// $this->output->set_content_type('image/'.$image->getImageFormat());
			// $this->output->set_output(file_get_contents('/tmp/test.png'));
			$this->output->set_content_type('image/'.$image->getImageFormat());
			$this->output->set_output($image->getImageBlob());
		}
}
