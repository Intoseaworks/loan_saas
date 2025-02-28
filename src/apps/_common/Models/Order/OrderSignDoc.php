<?php

namespace Common\Models\Order;

use Common\Models\User\User;
use Common\Services\Didio\DigioServer;
use Common\Traits\Model\StaticModel;
use Common\Utils\AadhaarApi\Api\EsignRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Order\OrderSignDoc
 *
 * @property int $id digio的docId
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property string $type 签约渠道类型
 * @property string $doc_id
 * @property string $doc_url 文档地址
 * @property int $contract_agreement_id 文档下载后关联contract_agreement表
 * @property int $status 状态 1:正常 2:已失效
 * @property string|null $created_time 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc whereContractAgreementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc whereDocId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc whereDocUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderSignDoc whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|OrderSignDoc orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderSignDoc query()
 */
class OrderSignDoc extends Model
{
    use StaticModel;

    public $timestamps = false;
    protected $table = 'order_sign_doc';
    protected $fillable = [];
    protected $hidden = [];

    /** @var string digio */
    const TYPE_DIGIO = 'digio';
    /** @var string AadhaarApi Esign */
    const TYPE_ESIGN = 'esign';

    /** @var int 状态：正常 */
    const STATUS_NORMAL = 1;
    /** @var int 状态：失效 */
    const STATUS_DISABLED = 2;

    public function textRules()
    {
        return [
            'array' => [
            ],
        ];
    }

    public function add($userId, $orderId, $docId, $type)
    {
        $this->user_id = $userId;
        $this->order_id = $orderId;
        $this->doc_id = $docId;
        $this->type = $type;
        $this->created_time = date('Y-m-d H:i:s');
        $this->status = self::STATUS_NORMAL;

        return $this->save();
    }

    public function getDocIdByOrderId($orderId, $signType)
    {
        $where = [
            ['order_id', $orderId],
            ['doc_id', '!=', ''],
            ['type', $signType],
            ['status', self::STATUS_NORMAL],
        ];

        if ($signType == self::TYPE_ESIGN) {
            // esign doc_id 过期时间为30分钟
            $where[] = ['created_time', '>', date('Y-m-d H:i:s', strtotime('- 20 minutes'))];
        }

        return $this->newQuery()
            ->where($where)
            ->orderByDesc('id')
            ->first();
    }

    public function toDisabled()
    {
        $this->status = self::STATUS_DISABLED;

        return $this->save();
    }

    public function saveDocUrl($docUrl)
    {
        $this->doc_url = $docUrl;
        return $this->save();
    }

    public function updateContractRelated($contractAgreementId)
    {
        $this->contract_agreement_id = $contractAgreementId;
        return $this->save();
    }

    public function getByDocId($docId)
    {
        return $this->newQuery()->where([
            'doc_id' => $docId,
        ])->first();
    }

    /**
     * 根据类型生成签名url
     * @return string
     * @throws \Exception
     */
    public function getSignUrlByType()
    {
        switch ($this->type) {
            case self::TYPE_DIGIO:
                $url = DigioServer::server()->getUrl($this->doc_id, $this->user->telephone);
                break;
            case self::TYPE_ESIGN:
                $url = EsignRequest::server()->buildSignUrl($this->doc_id);
                break;
            default:
                throw new \Exception('OrderSignDoc type error');
        }
        return $url;
    }

    public function user($class = User::class)
    {
        return $this->belongsTo($class, 'user_id', 'id');
    }

    public function order($class = Order::class)
    {
        return $this->belongsTo($class, 'order_id', 'id');
    }
}
