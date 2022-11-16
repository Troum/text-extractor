<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickException;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\TesseractOcrException;

class ExtractorController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @throws PageDoesNotExist
     * @throws PdfDoesNotExist
     * @throws TesseractOcrException
     */
    public function extract(Request $request): mixed
    {
        Storage::putFileAs('public/uploaded', $request->file('file'), 'uploaded.pdf');
        $uploaded = realpath(storage_path('app/public/uploaded/uploaded.pdf'));
        $pdf = new Pdf($uploaded);
        $content = '';
        File::cleanDirectory(storage_path('/app/public/converted'));
        for($i = 1; $i <= $pdf->getNumberOfPages(); $i++) {
            $pdf->setPage($i)
                ->saveImage(storage_path("/app/public/converted/image_$i.png"));
            $converted = realpath(storage_path("/app/public/converted/image_$i.png"));
            $content .= (new TesseractOCR($converted))
                ->executable('/opt/homebrew/bin/tesseract')
                ->lang('rus', 'eng')
                ->run() . '\n';
        }
        return $content;
    }
}
