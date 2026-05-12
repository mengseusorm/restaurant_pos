/**
 * LastQueryTime Cache Testing Script
 * 
 * This script demonstrates the two-tier caching system and helps verify
 * the performance improvements from the lastQueryTime optimization.
 * 
 * Usage in Vue DevTools Console or Component:
 * Copy and paste the test functions into your browser console
 */

// Test 1: Instant Cache Hit (< 30 seconds)
async function testInstantCache() {
    console.log('=== Test 1: Instant Cache Hit ===');
    
    // First call - will fetch from server
    console.time('First call');
    const result1 = await this.$store.dispatch('item/lists');
    console.timeEnd('First call');
    console.log('First call result:', {
        fromStateCache: result1.fromStateCache,
        fromCache: result1.fromCache,
        itemCount: result1.data?.data?.length || 0
    });
    
    // Immediate second call - should return from state cache
    console.time('Second call (instant cache)');
    const result2 = await this.$store.dispatch('item/lists');
    console.timeEnd('Second call (instant cache)');
    console.log('Second call result:', {
        fromStateCache: result2.fromStateCache,
        cacheAge: result2.cacheAge ? `${result2.cacheAge}ms` : 'N/A',
        itemCount: result2.data?.data?.length || 0
    });
    
    console.log('✅ Expected: Second call should be < 5ms with fromStateCache: true');
}

// Test 2: Cache Expiry and Server Check
async function testCacheExpiry() {
    console.log('=== Test 2: Cache Expiry (after 30+ seconds) ===');
    
    // First call
    console.time('First call');
    await this.$store.dispatch('item/lists');
    console.timeEnd('First call');
    
    console.log('⏳ Waiting 31 seconds for cache to expire...');
    await new Promise(resolve => setTimeout(resolve, 31000));
    
    // Second call after cache expiry
    console.time('Second call (after expiry)');
    const result = await this.$store.dispatch('item/lists');
    console.timeEnd('Second call (after expiry)');
    console.log('Second call result:', {
        fromStateCache: result.fromStateCache,
        fromCache: result.fromCache,
        message: result.fromCache ? 'Server checked, no updates' : 'Fresh data fetched'
    });
    
    console.log('✅ Expected: Should check server, return fromCache: true if no updates');
}

// Test 3: Force Refresh
async function testForceRefresh() {
    console.log('=== Test 3: Force Refresh ===');
    
    // Regular call to establish cache
    await this.$store.dispatch('item/lists');
    
    // Force refresh - bypasses all cache layers
    console.time('Force refresh');
    const result = await this.$store.dispatch('item/forceRefresh');
    console.timeEnd('Force refresh');
    console.log('Force refresh result:', {
        fromStateCache: result.fromStateCache,
        fromCache: result.fromCache,
        message: 'Fresh data always fetched'
    });
    
    console.log('✅ Expected: Should always fetch fresh data, no cache flags');
}

// Test 4: Rapid Successive Calls
async function testRapidCalls() {
    console.log('=== Test 4: Rapid Successive Calls ===');
    
    const iterations = 10;
    const results = [];
    
    for (let i = 0; i < iterations; i++) {
        const startTime = performance.now();
        const result = await this.$store.dispatch('item/lists');
        const endTime = performance.now();
        
        results.push({
            iteration: i + 1,
            duration: (endTime - startTime).toFixed(2) + 'ms',
            fromStateCache: result.fromStateCache,
            cacheAge: result.cacheAge
        });
    }
    
    console.table(results);
    
    const avgDuration = results.reduce((sum, r) => sum + parseFloat(r.duration), 0) / iterations;
    console.log(`📊 Average duration: ${avgDuration.toFixed(2)}ms`);
    console.log('✅ Expected: First call slower, subsequent calls < 5ms');
}

// Test 5: Multiple Modules Performance
async function testMultipleModules() {
    console.log('=== Test 5: Multiple Modules Performance ===');
    
    const modules = [
        { name: 'items', action: 'item/lists' },
        { name: 'printers', action: 'Printer/lists' },
        { name: 'printLabelSettings', action: 'printLabelSetting/lists' },
        { name: 'frontendSettings', action: 'frontendSetting/lists' }
    ];
    
    const results = [];
    
    for (const module of modules) {
        // First call
        const startTime1 = performance.now();
        await this.$store.dispatch(module.action);
        const duration1 = performance.now() - startTime1;
        
        // Second call (should hit instant cache)
        const startTime2 = performance.now();
        const result = await this.$store.dispatch(module.action);
        const duration2 = performance.now() - startTime2;
        
        results.push({
            module: module.name,
            firstCall: duration1.toFixed(2) + 'ms',
            secondCall: duration2.toFixed(2) + 'ms',
            improvement: ((duration1 - duration2) / duration1 * 100).toFixed(1) + '%',
            cached: result.fromStateCache ? '✅' : '❌'
        });
    }
    
    console.table(results);
    console.log('✅ Expected: All modules show 90%+ improvement on second call');
}

// Test 6: Clear Cache
async function testClearCache() {
    console.log('=== Test 6: Clear Cache ===');
    
    // Establish cache
    await this.$store.dispatch('item/lists');
    
    // Verify cache works
    console.time('Before clear (cached)');
    const result1 = await this.$store.dispatch('item/lists');
    console.timeEnd('Before clear (cached)');
    console.log('Before clear:', { fromStateCache: result1.fromStateCache });
    
    // Clear cache
    await this.$store.dispatch('item/clearCache');
    console.log('🗑️  Cache cleared');
    
    // Try again - should fetch fresh
    console.time('After clear (fresh)');
    const result2 = await this.$store.dispatch('item/lists');
    console.timeEnd('After clear (fresh)');
    console.log('After clear:', { 
        fromStateCache: result2.fromStateCache,
        fromCache: result2.fromCache
    });
    
    console.log('✅ Expected: After clear, should fetch fresh data');
}

// Run all tests
async function runAllTests() {
    console.clear();
    console.log('🚀 Running All Cache Optimization Tests\n');
    
    try {
        await testInstantCache();
        console.log('\n');
        
        await testRapidCalls();
        console.log('\n');
        
        await testMultipleModules();
        console.log('\n');
        
        await testForceRefresh();
        console.log('\n');
        
        await testClearCache();
        console.log('\n');
        
        console.log('✅ All tests completed!');
        console.log('\n⚠️  Note: Cache expiry test skipped (takes 31 seconds)');
        console.log('Run testCacheExpiry() manually if needed.');
        
    } catch (error) {
        console.error('❌ Test failed:', error);
    }
}

// Export test functions
if (typeof window !== 'undefined') {
    window.cacheTests = {
        testInstantCache,
        testCacheExpiry,
        testForceRefresh,
        testRapidCalls,
        testMultipleModules,
        testClearCache,
        runAllTests
    };
    
    console.log('✅ Cache test functions loaded!');
    console.log('Available tests:');
    console.log('  - cacheTests.testInstantCache()');
    console.log('  - cacheTests.testCacheExpiry()');
    console.log('  - cacheTests.testForceRefresh()');
    console.log('  - cacheTests.testRapidCalls()');
    console.log('  - cacheTests.testMultipleModules()');
    console.log('  - cacheTests.testClearCache()');
    console.log('  - cacheTests.runAllTests() // Run all except expiry test');
}

/**
 * Performance Monitoring Helper
 * 
 * Add this to your component to monitor cache performance in real-time:
 * 
 * mounted() {
 *   this.monitorCachePerformance();
 * }
 * 
 * methods: {
 *   async monitorCachePerformance() {
 *     const startTime = performance.now();
 *     const result = await this.$store.dispatch('item/lists');
 *     const endTime = performance.now();
 *     
 *     console.log('Cache Performance:', {
 *       duration: (endTime - startTime).toFixed(2) + 'ms',
 *       fromStateCache: result.fromStateCache,
 *       fromCache: result.fromCache,
 *       cacheAge: result.cacheAge ? result.cacheAge + 'ms' : 'N/A'
 *     });
 *   }
 * }
 */
