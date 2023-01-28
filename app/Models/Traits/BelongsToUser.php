<?php
/**
 * BelongsToUser.php
 *
 * @package   fitness-tracker
 * @copyright Copyright (c) 2023, Ashley Gibson
 * @license   MIT
 */

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property User $user
 */
trait BelongsToUser {
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
