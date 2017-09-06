<?php
	/**
	 * CodeMirror Customizer
	 * @author  biohzrdmx <github.com/biohzrdmx>
	 * @version 1.0
	 * @license MIT
	 */

	$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';

	$cur_dir = dirname(__FILE__);
	$path_base = "{$cur_dir}/data/codemirror.json";
	$path_assets = "{$cur_dir}/data/assets.json";

	if (! file_exists($path_assets) ) {
		header('Location: ?do=process');
	} else if (! file_exists($path_base) ) {
		header('Location: ?do=fetch');
	}

	switch ($do) {
		case 'fetch':
			# Fetch CDNJS JSON
			file_put_contents($path_base, file_get_contents('https://api.cdnjs.com/libraries/codemirror?fields=assets'));
			header('Location: ?do=process');
		break;
		case 'process':
			# Read CDNJS JSON
			$data = json_decode( file_get_contents($path_base) );
			# Initialize array
			$payload = array();
			# Process JSON
			foreach ($data->assets as $item) {
				$files = array();
				$files['lib'] = array();
				$files['keymap'] = array();
				$files['mode'] = array();
				$files['addon'] = array();
				foreach ($item->files as $file) {
					if ( preg_match('/(\.map|\.css)$/', $file) === 1 ) continue;
					$parts = explode('/', $file);
					switch ( count($parts) ) {
						case 1: // The main file
							$files['lib'] = $file;
						break;
						case 2: // Keymaps
							$type = isset( $parts[0] ) ? $parts[0] : '';
							$name = isset( $parts[1] ) ? $parts[1] : '';
							$name = preg_replace('/\.min\.js$/', '.js', $name);
							$files[$type][$name] = $file;
						break;
						case 3: // Addons
							$type = isset( $parts[0] ) ? $parts[0] : '';
							$cat = isset( $parts[1] ) ? $parts[1] : '';
							$name = isset( $parts[2] ) ? $parts[2] : '';
							$name = preg_replace('/\.min\.js$/', '.js', $name);
							$files[$type][$name] = $file;
						break;
					}
				}
				$payload[ $item->version ] = $files;
			}
			# Encode array
			$json = json_encode($payload);
			# Save array
			file_put_contents($path_assets, $json);
			header('Location: ?do=customize');
		break;
		case 'download':
			# Get POST data
			$lib = isset( $_POST['lib'] ) ? $_POST['lib'] : null;
			$version = isset( $_POST['version'] ) ? $_POST['version'] : null;
			$keymap = isset( $_POST['keymap'] ) ? $_POST['keymap'] : null;
			$minify = isset( $_POST['minify'] ) ? $_POST['minify'] : 0;
			$mode = isset( $_POST['mode'] ) ? $_POST['mode'] : null;
			$addon = isset( $_POST['addon'] ) ? $_POST['addon'] : null;
			# Build the base URL
			$base_url = "https://cdnjs.cloudflare.com/ajax/libs/codemirror/%s/%s";
			$data = '';
			# Check parameters
			if ($lib && $version && $lib) {
				# Fetch main library
				$lib_url = sprintf($base_url, $version, $lib);
				$data .= "// Base library v{$version}\n";
				$data .= file_get_contents($lib_url);
				# Fetch Keymaps
				if ($keymap) {
					foreach ($keymap as $item) {
						$item_url = sprintf($base_url, $version, $item);
						$data .= "\n// Include {$item}\n";
						$data .= file_get_contents($item_url);
					}
				}
				# Fetch Modes
				if ($mode) {
					foreach ($mode as $item) {
						$item_url = sprintf($base_url, $version, $item);
						$data .= "\n// Include {$item}\n";
						$data .= file_get_contents($item_url);
					}
				}
				# Fetch Addons
				if ($addon) {
					foreach ($addon as $item) {
						$item_url = sprintf($base_url, $version, $item);
						$data .= "\n// Include {$item}\n";
						$data .= file_get_contents($item_url);
					}
				}
			}
			# Send headers
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary");
			header("Content-disposition: attachment; filename=codemirror-{$version}.min.js");
			# Output data and exit
			echo $data;
			exit;
		break;
	}

	$assets = json_decode( file_get_contents($path_assets) );

	$versions = array_keys( get_object_vars($assets) );
	$version = isset($_REQUEST['version']) ? $_REQUEST['version'] : $versions[0];

	$files = $assets->$version;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CodeMirror Customizer</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
	<style>
		.boxfix-vert {
			padding: 1px 0;
		}
		.margins {
			margin: 15px;
		}
	</style>
</head>
<body>
	<div class="section">
		<div class="boxfix-vert">
			<div class="container">
				<div class="margins">
					<h2>CodeMirror Customizer</h2>
					<form action="?do=download" method="post">
						<input type="hidden" name="lib" value="<?php echo $files->lib; ?>">
						<h3>Library</h3>
						<div class="form-fields">
							<div class="form-group">
								<label for="version" class="control-label">Version</label>
								<select name="version" id="version" class="form-control">
									<?php
										if ($versions):
											foreach ($versions as $version_str):
									?>
										<option value="<?php echo $version_str; ?>" <?php echo ($version_str == $version ? 'selected="selected"' : ''); ?>><?php echo $version_str; ?></option>
									<?php
											endforeach;
										endif;
									?>
								</select>
							</div>
							<?php
								if ($files->keymap):
							?>
								<h3>Keymaps</h3>
								<div class="form-group">
									<div class="row">
										<?php
											foreach ($files->keymap as $name => $url):
										?>
											<div class="col-sm-3">
												<div class="checkbox">
													<label for="<?php echo "keymap-{$name}"; ?>"><input type="checkbox" name="keymap[]" id="<?php echo "keymap-{$name}"; ?>" value="<?php echo $url; ?>"><?php echo $name; ?></label>
												</div>
											</div>
										<?php
											endforeach;
										?>
									</div>
								</div>
							<?php
								endif;
							?>
							<?php
								if ($files->mode):
							?>
								<h3>Mode</h3>
								<div class="form-group">
									<div class="row">
										<?php
											foreach ($files->mode as $name => $url):
										?>
											<div class="col-sm-3">
												<div class="checkbox">
													<label for="<?php echo "mode-{$name}"; ?>"><input type="checkbox" name="mode[]" id="<?php echo "mode-{$name}"; ?>" value="<?php echo $url; ?>"><?php echo $name; ?></label>
												</div>
											</div>
										<?php
											endforeach;
										?>
									</div>
								</div>
							<?php
								endif;
							?>
							<?php
								if ($files->addon):
							?>
								<h3>Addon</h3>
								<div class="form-group">
									<div class="row">
										<?php
											foreach ($files->addon as $name => $url):
										?>
											<div class="col-sm-3">
												<div class="checkbox">
													<label for="<?php echo "addon-{$name}"; ?>"><input type="checkbox" name="addon[]" id="<?php echo "addon-{$name}"; ?>" value="<?php echo $url; ?>"><?php echo $name; ?></label>
												</div>
											</div>
										<?php
											endforeach;
										?>
									</div>
								</div>
							<?php
								endif;
							?>
						</div>
						<div class="form-actions text-center">
							<button type="submit" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-download-alt"></span> Download bundle</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<hr>
		<div class="text-center text-muted">
			<small>Coded by <a href="https://github.com/biohzrdmx" target="_blank">biohzrdmx</a></small>
			<br><br>
		</div>
	</div>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('[name=version]').on('change', function() {
				var el = $(this);
				window.location.href = '?version=' + el.val();
			});
		});
	</script>
</body>
</html>