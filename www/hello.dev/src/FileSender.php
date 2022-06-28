<?php

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use function PHPUnit\Framework\isEmpty;
use phpoffice\phpspreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
class FileSender implements Invoker
{
    public function __invoke(array $arguments = []):string
    {
        $prev = $arguments["prev"];
        $spr = new Spreadsheet();
        $sheet = $spr->getActiveSheet();
        $header = ["Login","Phone","Email","First Name"," 
        Last name","Chief email"," Status"," Whitelist",
            "Last active","Created at","Updated at"];
        array_unshift($prev,$header);
        $sheet->fromArray($prev,NULL,"A1");
        $writer = new Xlsx($spr);
        $writer->save(__DIR__."/myexcel.xlsx");
        $mail = new PHPMailer();
        $mail->setFrom('from@example.com', 'First Last');
        $mail->addAddress('gerasimenko778@mail.ru',
            'Kirill Gerasimenko');
        $mail->Subject = 'PHPMailer file sender';
        $mail->msgHTML("My message body");
        // Attach uploaded files
        $mail->addAttachment("./myexcel.xlsx");
        $r = $mail->send();
        unlink( __DIR__."/myexcel.xlsx");
        echo("Script sending mail have done succesfully!\n");
        return "Script sending mail have done succesfully!\n";
    }

    public function name():string
    {
        return "Files succesfully sent printing class";
    }


}