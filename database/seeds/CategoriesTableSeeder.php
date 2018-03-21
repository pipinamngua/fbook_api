<?php

use App\Eloquent\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::truncate();
        DB::table('categories')->insert([
            ['name_vi' => 'Bình luận văn học‎', 'name_en' => 'Literary review‎', 'name_jp' => '文学的解説'],
            ['name_vi' => 'Chính trị‎', 'name_en' => 'Politics', 'name_jp' => '政治'],
            ['name_vi' => 'Địa lý', 'name_en' => 'Geography‎', 'name_jp' => '地理'],
            ['name_vi' => 'Giáo khoa‎', 'name_en' => 'textbook‎', 'name_jp' => '教育‎'],
            ['name_vi' => 'Lịch sử‎', 'name_en' => 'History', 'name_jp' => '歴史'],
            ['name_vi' => 'Phi hư cấu‎', 'name_en' => 'Non-fiction', 'name_jp' => 'ノンフィクション‎'],
            ['name_vi' => 'Khoa học‎', 'name_en' => 'Science', 'name_jp' => '科学'],
            ['name_vi' => 'Kinh Tế - Quản Lý', 'name_en' => 'Economy - Management', 'name_jp' => '経済 - 管理‎'],
            ['name_vi' => 'Thiếu nhi‎', 'name_en' => 'Children', 'name_jp' => '児童'],
            ['name_vi' => 'Thiếu niên‎', 'name_en' => 'Teenager', 'name_jp' => '若者'],
            ['name_vi' => 'Tự lực‎', 'name_en' => 'Self-help', 'name_jp' => '自力'],
            ['name_vi' => 'Khoa học viễn tưởng', 'name_en' => 'Science fiction', 'name_jp' => '空想科学小説'],
            ['name_vi' => 'Truyện Ngắn - Ngôn Tình', 'name_en' => 'Short Story - Chinese Romance', 'name_jp' => 'ロマンチックな小説'],
            ['name_vi' => 'Truyện Cười -Tiếu Lâm', 'name_en' => 'Funny Stories - Humour', 'name_jp' => '落語'],
            ['name_vi' => 'Y Học - Sức Khỏe', 'name_en' => 'Medicine - Health', 'name_jp' => 'いがく‎'],
            ['name_vi' => 'Học Ngoại Ngữ', 'name_en' => 'Foreign Languages', 'name_jp' => '外国語を学ぶ‎'],
            ['name_vi' => 'Thể Thao - Nghệ Thuật', 'name_en' => 'Sports - Arts', 'name_jp' => 'スぽーツ -アート‎'],
            ['name_vi' => 'Trinh Thám - Hình Sự', 'name_en' => 'Detection - Crime', 'name_jp' => 'ていさついん - けいじ‎'],
            ['name_vi' => 'Văn Hóa - Tôn Giáo', 'name_en' => 'Culture - Religion', 'name_jp' => '-しゅうきょう‎'],
            ['name_vi' => 'Tử Vi - Phong Thủy', 'name_en' => 'Horoscope - Feng Shui', 'name_jp' => '風水‎'],
            ['name_vi' => 'Văn Học Việt Nam', 'name_en' => 'Vietnamese Literature', 'name_jp' => 'ベトナム文学‎'],
            ['name_vi' => 'Tiểu Thuyết Nước Ngoài', 'name_en' => 'Foreign Novel', 'name_jp' => 'がいこく の そてん‎'],
            ['name_vi' => 'Kinh Dị - Ma Quái', 'name_en' => 'Horror - Ghosts', 'name_jp' => 'かいき - ゆわい'],
            ['name_vi' => 'Huyền bí - Giả tưởng', 'name_en' => 'Mystery - Hypothesis', 'name_jp' => 'フィクション小説'],
            ['name_vi' => 'Hồi Ký - Tuỳ Bút', 'name_en' => 'Memoir - Essay', 'name_jp' => '回顧録'],
            ['name_vi' => 'Phiêu Lưu - Mạo Hiểm', 'name_en' => 'Adventure - Expedition', 'name_jp' => '冒険物語'],
            ['name_vi' => 'Tuổi Học Trò', 'name_en' => 'School Age', 'name_jp' => 'がくえん'],
            ['name_vi' => 'Cổ Tích - Thần Thoại', 'name_en' => 'Fairy Tail - Mythology', 'name_jp' => ' おとぎ - こえ‎'],
            ['name_vi' => 'Triết Học', 'name_en' => 'Philosophy', 'name_jp' => 'てつがく‎'],
            ['name_vi' => 'Kiếm Hiệp', 'name_en' => 'Knight-errant', 'name_jp' => '騎士'],
            ['name_vi' => 'Kiến Trúc - Xây Dựng', 'name_en' => 'Architecture - Construction', 'name_jp' => 'けんちく - かいせつ‎'],
            ['name_vi' => 'Nông - Lâm - Ngư', 'name_en' => 'Agriculture - Forestry - Fishery', 'name_jp' => '農業、林業、漁業に関する書籍'],
            ['name_vi' => 'Công Nghệ Thông Tin', 'name_en' => 'Information Technology', 'name_jp' => '情報技術'],
            ['name_vi' => 'Truyện Tranh', 'name_en' => 'Comic', 'name_jp' => 'まんが‎'],
            ['name_vi' => 'Tâm Lý - Kỹ Năng Sống', 'name_en' => 'Psychology - Life Skills', 'name_jp' => 'しんり - ラィフスキリ‎'],
        ]);
    }
}
