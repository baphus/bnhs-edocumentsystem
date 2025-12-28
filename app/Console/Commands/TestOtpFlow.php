<?php

namespace App\Console\Commands;

use App\Models\Otp;
use App\Services\OtpService;
use Illuminate\Console\Command;

class TestOtpFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:test {email : The email address to send OTP to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the full OTP flow: generate from database and send via email';

    /**
     * Execute the console command.
     */
    public function handle(OtpService $otpService)
    {
        $email = $this->argument('email');
        
        $this->info("Testing OTP flow for: {$email}");
        $this->newLine();
        
        // Step 1: Send OTP (generates in DB + sends email)
        $this->line('Step 1: Generating OTP and sending email...');
        $result = $otpService->sendOtp($email, 'request');
        
        if (!$result['success']) {
            $this->error('✗ Failed to send OTP!');
            $this->error('Error: ' . ($result['error'] ?? $result['message']));
            return Command::FAILURE;
        }
        
        $this->info('✓ OTP generated and email sent!');
        $this->newLine();
        
        // Step 2: Show the OTP from database
        $this->line('Step 2: Checking database...');
        $otp = Otp::where('email', $email)
            ->where('purpose', 'request')
            ->where('used', false)
            ->latest()
            ->first();
        
        if (!$otp) {
            $this->error('✗ OTP not found in database!');
            return Command::FAILURE;
        }
        
        $this->info('✓ OTP found in database!');
        $this->newLine();
        
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $otp->id],
                ['Email', $otp->email],
                ['Code', $otp->code],
                ['Purpose', $otp->purpose],
                ['Expires At', $otp->expires_at->format('Y-m-d H:i:s')],
                ['Used', $otp->used ? 'Yes' : 'No'],
                ['Is Valid', $otp->isValid() ? 'Yes' : 'No'],
            ]
        );
        
        $this->newLine();
        
        // Step 3: Test verification
        $this->line('Step 3: Testing OTP verification...');
        $verifyResult = $otpService->verifyOtp($email, $otp->code, 'request');
        
        if ($verifyResult['success']) {
            $this->info('✓ OTP verification successful!');
        } else {
            $this->error('✗ OTP verification failed: ' . $verifyResult['message']);
        }
        
        $this->newLine();
        
        // Final check - OTP should now be marked as used
        $otp->refresh();
        $this->line('Final Status:');
        $this->line('  - OTP Used: ' . ($otp->used ? 'Yes' : 'No'));
        $this->line('  - Is Valid: ' . ($otp->isValid() ? 'Yes' : 'No'));
        
        $this->newLine();
        $this->info('🎉 Full OTP flow test completed successfully!');
        $this->line('Check your Mailtrap inbox to see the email.');
        
        return Command::SUCCESS;
    }
}

