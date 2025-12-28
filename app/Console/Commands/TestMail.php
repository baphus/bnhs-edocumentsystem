<?php

namespace App\Console\Commands;

use App\Mail\OtpMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : The email address to send to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify mail configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Sending test email to: {$email}");
        $this->newLine();
        
        try {
            Mail::to($email)->send(new OtpMail('123456', 'request'));
            
            $this->info('✓ Test email sent successfully!');
            $this->newLine();
            $this->line('Mail Configuration:');
            $this->line('  - Driver: ' . config('mail.default'));
            $this->line('  - Host: ' . config('mail.mailers.smtp.host'));
            $this->line('  - Port: ' . config('mail.mailers.smtp.port'));
            $this->line('  - From: ' . config('mail.from.address'));
            $this->newLine();
            
            if (config('mail.default') === 'log') {
                $this->warn('Note: Using "log" driver - check storage/logs/laravel.log for the email.');
            } else {
                $this->info('Check your inbox or Mailpit/Mailtrap dashboard.');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Failed to send email!');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Please check your .env mail configuration.');
            
            return Command::FAILURE;
        }
    }
}
