<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeAccountCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $employee;

    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Employee $employee, $password)
    {
        $this->employee = $employee;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("[BKRM] Account Created")->view("components.employee-created");
    }
}
