<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Models\User\RecoveryCode;
use App\Http\Controllers\Controller;
use App\Traits\JsonRespondController;
use PragmaRX\Recovery\Recovery as PragmaRXRecovery;
use App\Http\Resources\Settings\RecoveryCode\RecoveryCodeCollection;

class RecoveryCodesController extends Controller
{
    use JsonRespondController;

    /**
     * Generate recovery codes.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Remove previous codes
        auth()->user()->recoveryCodes()
            ->each(function ($code) {
                $code->delete();
            });

        // Generate new codes
        $this->generate();

        $codes = auth()->user()->recoveryCodes()->get();

        return new RecoveryCodeCollection($codes);
    }

    /**
     * Get list of recovery codes (with POST request).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $codes = auth()->user()->recoveryCodes()->get();

        if (count($codes) == 0) {
            $this->generate();
            $codes = auth()->user()->recoveryCodes()->get();
        }

        return new RecoveryCodeCollection($codes);
    }

    private function generate()
    {
        // Generate new codes
        $recovery = new PragmaRXRecovery();
        $codes = $recovery->setCount(config('auth.recovery.count'))
                 ->setBlocks(config('auth.recovery.blocks'))
                 ->setChars(config('auth.recovery.chars'))
                 ->uppercase()
                 ->toArray();

        foreach ($codes as $code) {
            RecoveryCode::create([
                'account_id' => auth()->user()->account_id,
                'user_id' => auth()->user()->id,
                'recovery' => $code,
            ]);
        }
    }
}
