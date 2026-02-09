<?php

namespace App\Plugins;

/**---------------------------------------------
 * MW WP Form 連携クラス
 * ---------------------------------------------
 * - MW WP Form プラグイン専用の拡張処理をまとめる
 * - フォームの挙動・メール・初期設定・表示調整を一元管理
 * - テーマやテンプレートに MW WP Form のロジックを漏らさない
 */
class MwWpForm extends Plugin
{
  public function __construct()
  {
    if (!class_exists('MW_WP_Form'))
      return;
  }

  // 対象フォームID定数
  private const MWFORM_ID_CONTACT = 9999;

  /**
   * 初期化処理
   */
  public function boot(): void
  {
    // add_filter('mwform_validation_mw-wp-form-' . self::MWFORM_ID_CONTACT, [$this, 'validation'], 10, 3);
    // add_filter('mwform_admin_mail_mw-wp-form-' . self::MWFORM_ID_CONTACT, [$this, 'entryAutobackMyMail'], 10, 3);
    add_filter('mwform_default_content', [$this, 'defaultContent']);
    add_filter('mwform_default_settings', [$this, 'defaultSettings'], 10, 2);
    add_filter('mwform_custom_mail_tag', [$this, 'tag'], 10, 3);
    add_action('wp_footer', [$this, 'footerScript'], 9999);
    add_action('wp_print_footer_scripts', [$this, 'appendFormClass'], 9999);
    add_filter('user_can_richedit', [$this, 'disableVisualEditor']);
    add_action('load-post.php', [$this, 'disableVisualEditor']);
    add_action('load-post-new.php', [$this, 'disableVisualEditor']);
  }

  /**
   * フッター用スクリプト出力
   *
   * - ページ単位でフォーム表示を制御
   * - 規約文言の差し替え
   * - 確認・完了画面で不要要素を非表示
   */
  public function footerScript(): void
  {
    if (is_page('contact')) {
      echo <<<HTML
        <script>
        $(function() {
            $('.c-form__agreement .mwform-checkbox-field-text').html(
                '「<a href="/privacy" target="_blank" class="u-txt_ul">プライバシーポリシー</a>」に同意する'
            );
          });
        </script>
      HTML;
    }

    if (is_page(['confirm', 'thanks'])) {
      echo <<<HTML
        <script type="text/javascript">
         $(function() {
          if ($('.mw_wp_form_confirm, .mw_wp_form_complete').length) {
            $('.c-form__notes').hide();
            $('.c-form__privacy').hide();
            $('.c-form__agreement').hide();
          }
        });
        </script>
      HTML;
    }
  }

  /**
   * バリデーション制御
   *
   * - 条件付き必須チェック
   * - 選択肢制限
   */
  public function validation($Validation, $data, $Data)
  {
    if ($Data->get("hoge") == 'fuga') {
      $Validation->set_rule('hoge', 'noEmpty', array(
        'message' => 'fugaは必須項目です。'
      ));
    }

    $Validation->set_rule($Data->get("select"), 'in', array(
      'options' => array('select1', 'select2'),
      'message' => 'selectを選択してください'
    ));

    return $Validation;
  }

  /**
   * 管理者メール制御
   *
   * - フォーム内容に応じて送信先や件名を切り替える
   */
  public function entryAutobackMyMail($Mail_raw, $values, $Data)
  {
    switch ($Data->get('hoge')) {
      case 'fuga':
        $Mail_raw->to = "";
        $Mail_raw->bcc = "";
        $Mail_raw->subject = "";
      default:
        $Mail_raw->to = "";
        $Mail_raw->bcc = "";
        $Mail_raw->subject = "";
    }
    return $Mail_raw;
  }

  /**
   * カスタムメールタグ定義
   */
  public function tag(mixed $value, string $key, int $id): mixed
  {
    $tz = date_default_timezone_get();
    date_default_timezone_set('Asia/Tokyo');
    $time = date('Y年n月j日 H:i:s');
    date_default_timezone_set($tz);

    return match ($key) {
      '利用環境'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
      'IPアドレス' => $_SERVER['REMOTE_ADDR'] ?? '',
      'ホスト名'   => gethostbyaddr($_SERVER['REMOTE_ADDR'] ?? ''),
      '送信日時'   => $time,
      default  => $value,
    };
  }

  /**
   * MW WP Form 投稿タイプの
   * ビジュアルエディタを無効化
   */
  public function disableVisualEditor($can)
  {
    $screen = get_current_screen();

    if ($screen && $screen->post_type === 'mw-wp-form') {
      return false;
    }

    return $can;
  }

  /**
   * フォーム要素にクラスを追加
   */
  public function appendFormClass(): void
  {
    echo <<<HTML
    <script>
      if ($('.mw_wp_form'.length)){
        $('.mw_wp_form form').addClass('h-adr')
      }
    </script>
    HTML;
  }

  /**
   * フォーム初期設定
   *
   * - 自動返信メール
   * - 管理者メール
   * - バリデーションルール
   * - 遷移URL
   * - 完了メッセージ
   */
  public function defaultSettings(mixed $value, string $key): mixed
  {
    $profile = [
      'name'  => get_bloginfo('name'),
      'email' => get_bloginfo('admin_email'),
    ];

    $input = $this->buildInputText();

    return match ($key) {

      // 自動返信
      'mail_subject'          => 'お問い合わせありがとうございます',
      'mail_sender',
      'mail_reply_to',
      'mail_from'             => $profile['email'],
      'automatic_reply_email' => 'your_mail',

      'mail_content'          => <<<EOT
{your_name}様

この度は、お問い合わせいただきありがとうございます。

{$input}

=================================
{$profile['name']}
=================================
EOT,

      // 管理者
      'mail_to'               => $profile['email'],
      'admin_mail_subject'    => 'お問い合わせがありました',
      'admin_mail_sender'     => '{your_name}',
      'admin_mail_reply_to'   => '{your_mail}',
      'admin_mail_from'       => '{your_mail}',

      'admin_mail_content'    => <<<EOT
{your_name}様よりお問い合わせがありました。

{$input}

利用環境 : {利用環境}
送信元IPアドレス : {IPアドレス}
ホスト名 : {ホスト名}
送信日時 : {送信日時}
EOT,

      // バリデーション
      'validation'            => [
        ['target'            => 'your_name', 'noempty'            => true],
        ['target'            => 'your_name_kana', 'noempty'            => true, 'katakana'            => true],
        ['target'            => 'your_mail', 'noempty'            => true, 'mail'            => true],
        ['target'            => 'your_tel', 'noempty'            => true, 'tel'            => true],
        ['target'            => 'your_postal', 'noempty'            => true, 'zip'            => true],
        ['target'            => 'your_inquiry', 'noempty'            => true],
        ['target'            => 'recaptcha-v3'],
      ],

      // その他
      'usedb'                 => true,
      'input_url'             => '/contact/',
      'confirmation_url'      => '/contact/confirm/',
      'complete_url'          => '/contact/thanks/',
      'complete_message'      => <<<EOF
<p>この度は、お問い合わせいただき、ありがとうございます。<br>ご入力いただきましたメールアドレス宛に自動返信メールをお送りしております。<br>ご送信いただいた内容を確認後、折り返しご連絡させていただきます。</p>
<div class="c-form__button"><a href="../../" class="c-form__btn">トップページ</a></div>
EOF,

      default                 => $value,
    };
  }

  /**
   *
   */
  public function buildInputText(): string
  {
    return <<<EOT

─送信内容の確認─────────────────

[ お名前 ]　{your_name}
[ フリガナ ]　{your_name_kana}
[ 性別 ]　{your_gender}
[ ご住所 ]　〒{your_postal}　{your_address}
[ 電話番号 ]　{your_tel}
[ メールアドレス ]　{your_mail}
[ HPをご覧になったきっかけ ]　{your_trigger}

[ お問い合わせ内容 ]
{your_inquiry}

──────────────────────────

EOT;
  }

  /**
   * フォーム初期コンテンツ
   *
   * - MW WP Form のショートコードを用いた
   *   HTMLフォーム構造を定義
   */
  public function defaultContent(string $content): string
  {
    ob_start();
    echo <<<HTML
<p class="p-country-name" style="display:none!important">Japan</p>
<div class="c-form__head">

</div>
<div class="c-form__body">
    <table class="c-form__sheet">
        <tr>
            <th>
                <div class="c-form__ttl"><span>ご来社日時</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">[mwform_text name="your_date" class="c-form__input -text -middle js-datepicker" show_error="false"]</div>
                </div>
                <div class="c-form__error">[mwform_error keys="your_date"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>お名前</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">[mwform_text name="your_name" class="c-form__input -text -middle" show_error="false"]</div>
                </div>
                <div class="c-form__error">[mwform_error keys="your_name"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>フリガナ</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">[mwform_text name="your_name_kana" class="c-form__input -text -middle" show_error="false"]</div>
                </div>
                <div class="c-form__error">[mwform_error keys="your_name_kana"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>お名前</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">[mwform_text name="your_name01" class="c-form__input -text -half" show_error="false" placeholder="姓"][mwform_text name="your_name02" class="c-form__input -text -half" show_error="false" placeholder="名"]</div>
                </div>
                <div class="c-form__error">[mwform_error keys="your_name01,your_name02"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>フリガナ</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">
                      [mwform_text name="your_name_kana01" class="c-form__input -text -half"  show_error="false" placeholder="セイ"]
                      [mwform_text name="your_name_kana02" class="c-form__input -text -half" show_error="false" placeholder="メイ"]
                    </div>
                </div>
                <div class="c-form__error">[mwform_error keys="your_name_kana01,your_name_kana02"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>性別</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">[mwform_radio name="your_gender" class="c-form__radio" children="男性,女性"  value="男性" show_error="false"]</div>
                </div>
                <div class="c-form__error">[mwform_error keys="your_gender"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>電話番号</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">[mwform_text name="your_tel" class="c-form__input -text -short" show_error="false"]</div>
                </div>
                <div class="c-form__notes">ハイフンなしで入力してください</div>
                <div class="c-form__error">[mwform_error keys="your_tel"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>ご住所</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <table class="c-form__rowgroup h-adr">
                        <tr>
                            <th>
                                <div class="c-form__ttl">郵便番号</div>
                            </th>
                            <td>
                                <div class="c-form__field">
                                  〒[mwform_text name="your_postal" class="p-postal-code c-form__input -text"  show_error="false" size="8"]
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <div class="c-form__ttl">都道府県</div>
                            </th>
                            <td>
                                <div class="c-form__field">
                                    <span class="p-country-name" style="display:none;">Japan</span>
                                    [mwform_select name="your_pref" children=":--,北海道,青森県,岩手県,宮城県,秋田県,山形県,福島県,茨城県,栃木県,群馬県,埼玉県,千葉県,東京都,神奈川県,新潟県,富山県,石川県,福井県,山梨県,長野県,岐阜県,静岡県,愛知県,三重県,滋賀県,京都府,大阪府,兵庫県,奈良県,和歌山県,鳥取県,島根県,岡山県,広島県,山口県,徳島県,香川県,愛媛県,高知県,福岡県,佐賀県,長崎県,熊本県,大分県,宮崎県,鹿児島県,沖縄県" class="p-region c-form__select" show_error="false"]
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <div class="c-form__ttl">市区町村・番地</div>
                            </th>
                            <td>
                                <div class="c-form__field">[mwform_text name="your_locality" class="p-locality p-street-address c-form__input -text -long" show_error="false"]</div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <div class="c-form__ttl">ビル・マンション名</div>
                            </th>
                            <td>
                                <div class="c-form__field">[mwform_text name="your_exaddress" class="p-extended-address c-form__input -text -long" show_error="false"]</div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="c-form__error">[mwform_error keys="your_postal,your_pref,your_locality"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>メールアドレス</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">[mwform_email name="your_mail" class="c-form__input -text -middle" show_error="false"]</div>
                </div>
                <div class="c-form__error">[mwform_error keys="your_mail"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>HPをご覧になったきっかけ</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">[mwform_checkbox name="your_trigger" class="c-form__check" children="ホームズ,SUUMO,友達の紹介,チラシ・パンフレット" separator="," show_error="false"]</div>
                </div>
                <div class="c-form__error">[mwform_error keys="your_trigger"]</div>
            </td>
        </tr>
        <tr>
            <th>
                <div class="c-form__ttl -required"><span>お問い合わせ内容</span></div>
            </th>
            <td>
                <div class="c-form__row">
                    <div class="c-form__field">[mwform_textarea name="your_inquiry" class="c-form__input -textarea -long" show_error="false"]</div>
                </div>
                <div class="c-form__error">[mwform_error keys="your_inquiry"]</div>
            </td>
        </tr>
    </table>
</div>
<div class="c-form__foot">
    <div class="c-form__privacy">
        <div class="c-form__agreement">
            [mwform_checkbox name="your_agreement" children="同意する" separator="," show_error="false" class="c-form__check"]
        </div>
    </div>
    <div class="u-ta_center">[mwform_error keys="your_agreement"]</div>
    <div class="u-ta_center">[mwform_hidden name="recaptcha-v3" value="false"][mwform_error keys="recaptcha-v3"]</div>
    <div class="c-form__button">
        [mwform_bsubmit name="btn_submit" class="c-form__btn c-btn -submit" value="submit"]送信する[/mwform_bsubmit]
        [mwform_bconfirm class="c-form__btn c-btn -confirm" value="confirm"]確認画面へ[/mwform_bconfirm]
        [mwform_bback class="c-form__btn c-btn -back" value="back"]戻る[/mwform_bback]
    </div>
</div>
HTML;
    return ob_get_clean();
  }
}