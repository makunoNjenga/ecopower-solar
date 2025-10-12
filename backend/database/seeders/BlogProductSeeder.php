<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the blog_product pivot table to attach related products to blogs
 */
class BlogProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Get blog IDs by slug
        $solarPanelGuide   = DB::table('blogs')->where('slug', 'ultimate-guide-choosing-solar-panels-home')->value('id');
        $batteryGuide      = DB::table('blogs')->where('slug', 'solar-battery-storage-worth-investment')->value('id');
        $goingSolar        = DB::table('blogs')->where('slug', 'top-5-benefits-going-solar-2025')->value('id');
        $inverterGuide     = DB::table('blogs')->where('slug', 'understanding-solar-inverters-types-selection-guide')->value('id');
        $offGridVsGridTied = DB::table('blogs')->where('slug', 'off-grid-vs-grid-tied-solar-which-right')->value('id');

        // Get product IDs by SKU
        $monoPanel      = DB::table('products')->where('sku', 'SP-MONO-450W')->value('id');
        $polyPanel      = DB::table('products')->where('sku', 'SP-POLY-350W')->value('id');
        $bifacialPanel  = DB::table('products')->where('sku', 'SP-BIFA-550W')->value('id');
        $hybridInverter = DB::table('products')->where('sku', 'INV-HYB-5KW')->value('id');
        $gridInverter   = DB::table('products')->where('sku', 'INV-GRID-10KW')->value('id');
        $lithiumBattery = DB::table('products')->where('sku', 'BAT-LIPO-10KWH')->value('id');
        $batteryModule  = DB::table('products')->where('sku', 'BAT-MOD-5KWH')->value('id');
        $mpptController = DB::table('products')->where('sku', 'CC-MPPT-60A')->value('id');
        $kit3kw         = DB::table('products')->where('sku', 'KIT-3KW-COMP')->value('id');
        $kit5kwOffGrid  = DB::table('products')->where('sku', 'KIT-5KW-OFFGRID')->value('id');

        $attachments = [];

        // Solar Panel Guide - attach solar panels
        if ($solarPanelGuide) {
            $attachments[] = ['blog_id' => $solarPanelGuide, 'product_id' => $monoPanel, 'sort_order' => 0];
            $attachments[] = ['blog_id' => $solarPanelGuide, 'product_id' => $polyPanel, 'sort_order' => 1];
            $attachments[] = ['blog_id' => $solarPanelGuide, 'product_id' => $bifacialPanel, 'sort_order' => 2];
        }

        // Battery Guide - attach batteries
        if ($batteryGuide) {
            $attachments[] = ['blog_id' => $batteryGuide, 'product_id' => $lithiumBattery, 'sort_order' => 0];
            $attachments[] = ['blog_id' => $batteryGuide, 'product_id' => $batteryModule, 'sort_order' => 1];
            $attachments[] = ['blog_id' => $batteryGuide, 'product_id' => $hybridInverter, 'sort_order' => 2];
        }

        // Going Solar - attach complete kits
        if ($goingSolar) {
            $attachments[] = ['blog_id' => $goingSolar, 'product_id' => $kit3kw, 'sort_order' => 0];
            $attachments[] = ['blog_id' => $goingSolar, 'product_id' => $kit5kwOffGrid, 'sort_order' => 1];
            $attachments[] = ['blog_id' => $goingSolar, 'product_id' => $monoPanel, 'sort_order' => 2];
        }

        // Inverter Guide - attach inverters
        if ($inverterGuide) {
            $attachments[] = ['blog_id' => $inverterGuide, 'product_id' => $hybridInverter, 'sort_order' => 0];
            $attachments[] = ['blog_id' => $inverterGuide, 'product_id' => $gridInverter, 'sort_order' => 1];
            $attachments[] = ['blog_id' => $inverterGuide, 'product_id' => $mpptController, 'sort_order' => 2];
        }

        // Off-Grid vs Grid-Tied - attach relevant products
        if ($offGridVsGridTied) {
            $attachments[] = ['blog_id' => $offGridVsGridTied, 'product_id' => $kit5kwOffGrid, 'sort_order' => 0];
            $attachments[] = ['blog_id' => $offGridVsGridTied, 'product_id' => $kit3kw, 'sort_order' => 1];
            $attachments[] = ['blog_id' => $offGridVsGridTied, 'product_id' => $lithiumBattery, 'sort_order' => 2];
            $attachments[] = ['blog_id' => $offGridVsGridTied, 'product_id' => $hybridInverter, 'sort_order' => 3];
        }

        // Add timestamps to all attachments
        $now = now();
        foreach ($attachments as &$attachment) {
            $attachment['created_at'] = $now;
            $attachment['updated_at'] = $now;
        }

        DB::table('blog_product')->insert($attachments);

        $this->command->info('Blog-Product relationships seeded successfully! ' . count($attachments) . ' relationships created.');
    }
}
