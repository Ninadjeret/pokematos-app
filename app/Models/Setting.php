<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static $allowedSettings = [
        'timing_before_eclosion' => ['default' => 60, 'type' => 'integer'],
        'timing_after_eclosion' => ['default' => 45, 'type' => 'integer'],
    ];

    public static function get($value = null)
    {
        $return = [];
        $settings = Setting::all();

        foreach (Setting::$allowedSettings as $settingKey => $setting_data) {
            $return[$settingKey] = $setting_data['default'];
            if ($settings) {
                foreach ($settings as $setting) {
                    if ($setting->key == $settingKey) {
                        switch ($setting_data['type']) {
                            case 'string':
                                $return[$settingKey] = $setting->value;
                                break;
                            case 'integer':
                                $return[$settingKey] = (int) $setting->value;
                                break;
                            case 'boolean':
                                $return[$settingKey] = (bool) $setting->value;
                            case 'array':
                                $return[$settingKey] = json_decode($setting->value);
                        }
                    }
                }
            }
        }
        if (!empty($value)) return $return[$value];
        return (object) $return;
    }

    public static function change($settings)
    {
        foreach ($settings as $key => $value) {

            if (is_array($value)) $value = json_encode($value);

            $setting = Setting::where('key', $key)
                ->first();
            if (!empty($setting)) {
                $setting->update(['value' => $value]);
            } else {
                $setting = Setting::create([
                    'key' => $key,
                    'value' => $value
                ]);
            }
        }

        return true;
    }
}