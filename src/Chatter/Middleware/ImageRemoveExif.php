<?php

namespace Chatter\Middleware;

class ImageRemoveExif
{
	public function __invoke($request, $response, $next)
	{
		$files = $request->getUploadedFiles();
		$newfile = $files['file'];
		$newfile_type = $newfile->getClientMediaType();
		$uploadFilename = $newfile->getClientFilename();
		$newfile->moveTo("assets/images/tmp/$uploadFilename");
		$pngfile = "assets/images/" . substr($uploadFilename, 0, -4) . "png"; // Drop the original extension and replace it with png
		
		// It is not a png, we need to write a new file
		if('image/jpeg' == $newfile_type) {
			$_img = imagecreatefromjpeg("assets/images/tmp/" . $uploadFilename);
			imagepng($_img, $pngfile);
		}
		
		$response = $next($request, $response);
		return $response;
	}
}