<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    // اسم الجدول
    protected $table = 'settings';

    // الحقول القابلة للتعبئة
    protected $fillable = [
        'title',
        'description',
        'more_desc',
        'footer_date',
        'footer_text',
        'logo',
        'version',
        'tags',
        'mobile',
        'address',
        'contact_email',
        'market_situation',
        'currency',
        'close_status',
        'close_text',
    ];

    /**
     * تحديث إعدادات الموقع.
     *
     * @param Setting $obj
     * @param string|null $title
     * @param string|null $description
     * @param string $more_desc
     * @param string|null $footer_date
     * @param string|null $footer_text
     * @param string|null $logo
     * @param string|null $version
     * @param string|null $tags
     * @param string|null $mobile
     * @param string|null $address
     * @param string|null $contact_email
     * @param string|null $market_situation
     * @param string $currency
     * @param int|null $close_status
     * @param string|null $close_text
     * @return Setting
     */
    public function updateSettings($obj, $title, $description, $more_desc, $footer_date, $footer_text, $logo, $version, $tags, $mobile, $address, $contact_email, $market_situation, $currency, $close_status, $close_text)
    {
        $obj->title = $title;
        $obj->description = $description;
        $obj->more_desc = $more_desc;
        $obj->footer_date = $footer_date;
        $obj->footer_text = $footer_text;
        $obj->logo = $logo;
        $obj->version = $version;
        $obj->tags = $tags;
        $obj->mobile = $mobile;
        $obj->address = $address;
        $obj->contact_email = $contact_email;
        $obj->market_situation = $market_situation;
        $obj->currency = $currency;
        $obj->close_status = $close_status;
        $obj->close_text = $close_text;

        $obj->save();

        return $obj;
    }

    /**
     * تحديث حالة الإعداد (close_status أو status).
     *
     * @param int $id
     * @param int $status
     * @return int
     */
    public function updateStatus($id, $status)
    {
        return $this->where('id', $id)->update(['close_status' => $status]);
    }

    /**
     * جلب إعداد معين حسب ID.
     *
     * @param int $id
     * @return Setting|null
     */
    public function getSetting($id)
    {
        return $this->find($id);
    }

    /**
     * جلب كل الإعدادات.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPages()
    {
        return $this->get();
    }

    /**
     * جلب إعداد معين حسب الاسم (title).
     *
     * @param string $name
     * @return Setting|null
     */
    public function getPageByName($name)
    {
        return $this->where('title', $name)->first();
    }

    /**
     * جلب الإعدادات مع إمكانية البحث حسب العنوان.
     *
     * @param string|null $page
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPages($page = null)
    {
        return $this->when($page, function ($query, $page) {
            $query->where('title', 'LIKE', '%' . $page . '%');
        })->get();
    }
}
