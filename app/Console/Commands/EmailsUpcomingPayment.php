<?php

namespace App\Console\Commands;

use App\License;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Contracts\Logging\Log;

class EmailsUpcomingPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:upcoming-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send out emails for upcoming charges';

    private $mailer;
    private $log;

    /**
     * SendUpcomingChargeEmails constructor.
     *
     * @param Mailer $mailer
     * @param Log $log
     */
    public function __construct(Mailer $mailer, Log $log)
    {
        parent::__construct();

        $this->mailer = $mailer;
        $this->log = $log;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $treshold = date('Y-m-d H:i:s', strtotime('+10 days'));
        $now = date('Y-m-d H:i:s');

        /** @var License[] $licenses */
        $licenses = License::where('expires_at', '<=', $treshold)
            ->where('status', 'active')
            ->where('expires_at', '>', $now)
            ->where('interval', 'year')
            ->with('user')
            ->get();
        $licenses->each([$this, 'handle_license']);
    }

    public function handle_license( License $license )
    {
        // check if already reminded in last 10 days
        $tresholdDate = new Carbon('-10 days');
        if( $license->last_reminded_at >= $tresholdDate) {
            return;
        }

        $this->mailer->send('emails.upcoming-payment', ['license' => $license, 'user' => $license->user], function(Message $message) use($license) {
            $from = config('mail.from.address');
            $message
                ->to($license->user->email, $license->user->name)
                ->subject('Your upcoming Boxzilla payment')
                ->replyTo($from);
        });

        // update last_reminded_at so we don't email again
        $license->last_reminded_at = Carbon::now();
        $license->save();

        // log some info
        $this->log->info(sprintf('Emailed %s about upcoming payment for license #%d', $license->user->email, $license->id));
        $this->info(sprintf('Emailed %s about upcoming payment for license #%d', $license->user->email, $license->id));
    }
}
