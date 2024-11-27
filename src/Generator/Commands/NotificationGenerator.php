<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Printer;
use Nette\PhpGenerator\PhpFile;

class NotificationGenerator extends FileGeneratorCommand
{
    public static function getCommandName(): string
    {
        return 'apiato:make:notification';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Notification for a Container';
    }

    public static function getFileType(): string
    {
        return 'notification';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
        ];
    }

    protected function askCustomInputs(): void
    {
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Notifications/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Notifications');

        // imports
        $parentNotificationFullPath = 'App\Ship\Parents\Notifications\Notification';
        $namespace->addUse($parentNotificationFullPath, 'ParentNotification');
        $mailMessageFullPath = 'Illuminate\Notifications\Messages\MailMessage';
        $namespace->addUse($mailMessageFullPath);
        $queueableFullPath = 'Illuminate\Bus\Queueable';
        $namespace->addUse($queueableFullPath);
        $userModelFullPath = 'App\Containers\AppSection\User\Models\User';
        $namespace->addUse($userModelFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentNotificationFullPath);
        $class->addTrait($queueableFullPath);

        // via method
        $viaMethod = $class->addMethod('via')
            ->setPublic()
            ->setReturnType('array')
            ->setBody("return ['mail'];")
        ->addParameter('notifiable');

        // toMail method
        $toMailMethod = $class->addMethod('toMail')
            ->setPublic()
            ->setReturnType($mailMessageFullPath)
        ->setBody("
return (new MailMessage())
    ->subject('Email Verified')
    ->line('Your email has been verified.');
");
        $toMailMethod->addParameter('notifiable')
            ->setType($userModelFullPath);

        return $printer->printFile($file);
    }
}
