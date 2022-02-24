<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\ReasonForAction;

class OrderItemDispute extends Model
{
    use HasFactory,Uuid;

    protected $fillable = ['auto_id', 'dispute_raised_by', 'dispute_raised_against', 'order_item_id', 'products_services_book_id', 'quantity', 'amount_to_be_returned', 'reason_id_for_dispute', 'dispute', 'reply', 'date_of_dispute_completed', 'reason_id_for_review', 'review_by_seller', 'dispute_status', 'reason_id_for_dispute_decline', 'reason_for_dispute_decline', 'reason_id_for_review_decline', 'reason_for_review_decline', 'admin_remarks', 'dispute_images', 'review_images', 'review_decline_images'];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class,'order_item_id','id');
    }

    public function disputeRaisedBy()
    {
        return $this->belongsTo(User::class,'dispute_raised_by','id');
    }

    public function disputeRaisedAgainst()
    {
        return $this->belongsTo(User::class,'dispute_raised_against','id');
    }

    public function reasonIdForDisputeDecline()
    {
        return $this->belongsTo(ReasonForAction::class,'reason_id_for_dispute_decline','id');
    }

    public function reasonIdForReviewDecline()
    {
        return $this->belongsTo(ReasonForAction::class,'reason_id_for_review_decline','id');
    }
}
