<?php

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use function PHPUnit\Framework\isEmpty;
use phpoffice\phpspreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
class ByeWorld implements Invoker
{
    public string $line;

//    public function __construct($line = "Hello, world!\n"){
//        $this->line = $line;
//    }

    public function __invoke(array $arguments = [])
    {


        $prev = $arguments["prev"];
        //print_r($prev);
        $spr = new Spreadsheet();
        $sheet = $spr->getActiveSheet();
        $header = ["Login","Phone","Email","First Name"," Last name","Chief email"," Status"," Whitelist","Last active","Created at","Updated at"];
        array_unshift($prev,$header);
        $sheet->fromArray($prev,NULL,"A1");
        $writer = new Xlsx($spr);
        $writer->save(__DIR__."/myexcel.xlsx");
        $mail = new PHPMailer();
        $mail->setFrom('from@example.com', 'First Last');
        $mail->addAddress('gerasimenko778@mail.ru', 'Kirill Gerasimenko');
        $mail->Subject = 'PHPMailer file sender';
        $mail->msgHTML("My message body");
        // Attach uploaded files
        $mail->addAttachment("./myexcel.xlsx");
        $r = $mail->send();
        unlink( __DIR__."/myexcel.xlsx");
        $this->line = "Script sending mail have done succesfully!\n";

        echo($this->line);
        return $this->line;
    }

    public function name():string
    {
        return "Bye World printing class";
    }


}