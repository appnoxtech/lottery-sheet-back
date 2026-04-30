@component('mail::message')
# Request Processed Successfully

Dear {{ $lotteryRequest->name }},

We are pleased to inform you that your lottery request has been processed and accepted.

**Request Details:**
- **Amount:** ${{ number_format($lotteryRequest->amount, 2) }}
- **Type:** {{ $lotteryRequest->lottery_type }}
- **Numbers:** {{ is_array($lotteryRequest->lottery_numbers) ? implode(', ', $lotteryRequest->lottery_numbers) : $lotteryRequest->lottery_numbers }}

Thank you for choosing Curacao Lottery. We wish you the best of luck!

Thanks,<br>
The {{ config('app.name') }} Team
@endcomponent
