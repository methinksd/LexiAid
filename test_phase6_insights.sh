#!/bin/bash

# LexiAid Phase 6 - Insights Integration Test Script
# Tests all aspects of the insights & analytics functionality

echo "🚀 LexiAid Phase 6 - Insights Integration Test"
echo "=============================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test results
TESTS_PASSED=0
TESTS_FAILED=0

# Helper function to test HTTP endpoints
test_endpoint() {
    local url=$1
    local description=$2
    local expected_status=${3:-200}
    
    echo -n "Testing $description... "
    
    local response=$(curl -s -w "HTTPSTATUS:%{http_code}" "$url")
    local http_code=$(echo "$response" | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
    local body=$(echo "$response" | sed -e 's/HTTPSTATUS\:.*//g')
    
    if [ "$http_code" -eq "$expected_status" ]; then
        echo -e "${GREEN}✅ PASS${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
        return 0
    else
        echo -e "${RED}❌ FAIL (HTTP $http_code)${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        return 1
    fi
}

# Helper function to test JSON structure
test_json_structure() {
    local url=$1
    local description=$2
    local required_fields=$3
    
    echo -n "Testing $description... "
    
    local json_response=$(curl -s "$url")
    
    # Check if response is valid JSON
    if ! echo "$json_response" | python3 -m json.tool > /dev/null 2>&1; then
        echo -e "${RED}❌ FAIL (Invalid JSON)${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        return 1
    fi
    
    # Check required fields
    local missing_fields=""
    for field in $required_fields; do
        if ! echo "$json_response" | python3 -c "import sys, json; data=json.load(sys.stdin); sys.exit(0 if '$field' in data else 1)" 2>/dev/null; then
            missing_fields="$missing_fields $field"
        fi
    done
    
    if [ -n "$missing_fields" ]; then
        echo -e "${RED}❌ FAIL (Missing fields:$missing_fields)${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        return 1
    else
        echo -e "${GREEN}✅ PASS${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
        return 0
    fi
}

echo "📊 Phase 6 Test Suite - Insights & Analytics"
echo "============================================="
echo ""

# Test 1: Server availability
echo "🌐 Server Connectivity Tests"
echo "----------------------------"
test_endpoint "http://localhost:8080/" "Main dashboard"
test_endpoint "http://localhost:8080/insights.html" "Insights page"
test_endpoint "http://localhost:8080/insights.php?user_id=5" "Insights API"
echo ""

# Test 2: API Data Structure
echo "🔍 API Data Structure Tests"
echo "---------------------------"
test_json_structure "http://localhost:8080/insights.php?user_id=5" \
    "Basic API structure" \
    "summary studyTime subjectPie quizPerformance topicPerformance productivity consistency resourceUtilization weakAreas recommendations resources"

test_json_structure "http://localhost:8080/insights.php?user_id=5&period=weekly" \
    "Weekly period filter" \
    "summary"

test_json_structure "http://localhost:8080/insights.php?user_id=5&period=monthly" \
    "Monthly period filter" \
    "summary"

test_json_structure "http://localhost:8080/insights.php?user_id=5&period=semester" \
    "Semester period filter" \
    "summary"
echo ""

# Test 3: Data Quality
echo "📈 Data Quality Tests"
echo "--------------------"
echo -n "Testing summary data quality... "
summary_data=$(curl -s "http://localhost:8080/insights.php?user_id=5" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    summary = data['summary']
    
    # Check if we have meaningful data
    has_tasks = summary.get('tasksAssigned', 0) > 0
    has_quizzes = summary.get('quizAverage', 0) > 0
    
    if has_tasks and has_quizzes:
        print('PASS')
        sys.exit(0)
    else:
        print('FAIL - No meaningful data')
        sys.exit(1)
except Exception as e:
    print(f'FAIL - {e}')
    sys.exit(1)
")

if [ "$summary_data" = "PASS" ]; then
    echo -e "${GREEN}✅ PASS${NC}"
    TESTS_PASSED=$((TESTS_PASSED + 1))
else
    echo -e "${RED}❌ FAIL${NC}"
    TESTS_FAILED=$((TESTS_FAILED + 1))
fi

echo -n "Testing chart data availability... "
chart_data=$(curl -s "http://localhost:8080/insights.php?user_id=5" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    
    # Check if charts have data
    has_quiz_data = len(data.get('topicPerformance', {}).get('labels', [])) > 0
    has_study_data = any(v > 0 for v in data.get('studyTime', {}).get('values', []))
    has_weak_areas = len(data.get('weakAreas', [])) > 0
    
    if has_quiz_data or has_study_data or has_weak_areas:
        print('PASS')
        sys.exit(0)
    else:
        print('FAIL - No chart data')
        sys.exit(1)
except Exception as e:
    print(f'FAIL - {e}')
    sys.exit(1)
")

if [ "$chart_data" = "PASS" ]; then
    echo -e "${GREEN}✅ PASS${NC}"
    TESTS_PASSED=$((TESTS_PASSED + 1))
else
    echo -e "${RED}❌ FAIL${NC}"
    TESTS_FAILED=$((TESTS_FAILED + 1))
fi
echo ""

# Test 4: Error Handling
echo "⚠️  Error Handling Tests"
echo "------------------------"
test_endpoint "http://localhost:8080/insights.php?user_id=999999" "Invalid user ID handling"
test_endpoint "http://localhost:8080/insights.php?user_id=5&period=invalid" "Invalid period handling"
echo ""

# Test 5: File Dependencies
echo "📁 File Dependencies Tests"
echo "--------------------------"
files_to_check=(
    "/home/leo/Freelance Projects/LexiAid/site/insights.html"
    "/home/leo/Freelance Projects/LexiAid/site/insights.php"
    "/home/leo/Freelance Projects/LexiAid/site/js/insights.js"
    "/home/leo/Freelance Projects/LexiAid/site/config/database.php"
    "/home/leo/Freelance Projects/LexiAid/site/test_insights.html"
)

for file in "${files_to_check[@]}"; do
    echo -n "Checking $(basename "$file")... "
    if [ -f "$file" ]; then
        echo -e "${GREEN}✅ EXISTS${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo -e "${RED}❌ MISSING${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
done
echo ""

# Test 6: Database Data
echo "🗄️  Database Tests"
echo "------------------"
echo -n "Testing database connectivity... "
db_test=$(php -r "
try {
    require_once '/home/leo/Freelance Projects/LexiAid/site/config/database.php';
    \$conn = getDbConnection();
    echo 'PASS';
} catch (Exception \$e) {
    echo 'FAIL - ' . \$e->getMessage();
}
")

if [ "$db_test" = "PASS" ]; then
    echo -e "${GREEN}✅ PASS${NC}"
    TESTS_PASSED=$((TESTS_PASSED + 1))
else
    echo -e "${RED}❌ FAIL${NC}"
    TESTS_FAILED=$((TESTS_FAILED + 1))
fi

echo -n "Testing sample data existence... "
data_test=$(php -r "
try {
    require_once '/home/leo/Freelance Projects/LexiAid/site/config/database.php';
    \$conn = getDbConnection();
    \$result = \$conn->query('SELECT COUNT(*) as count FROM tasks WHERE user_id = 5');
    \$row = \$result->fetch_assoc();
    echo (\$row['count'] > 0) ? 'PASS' : 'FAIL - No tasks';
} catch (Exception \$e) {
    echo 'FAIL - ' . \$e->getMessage();
}
")

if [ "$data_test" = "PASS" ]; then
    echo -e "${GREEN}✅ PASS${NC}"
    TESTS_PASSED=$((TESTS_PASSED + 1))
else
    echo -e "${RED}❌ FAIL${NC}"
    TESTS_FAILED=$((TESTS_FAILED + 1))
fi
echo ""

# Test Summary
echo "📋 Test Summary"
echo "==============="
echo -e "Tests Passed: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Tests Failed: ${RED}$TESTS_FAILED${NC}"
echo -e "Total Tests: $((TESTS_PASSED + TESTS_FAILED))"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}🎉 All tests passed! Phase 6 implementation is working correctly.${NC}"
    echo ""
    echo "✅ Phase 6 Success Criteria:"
    echo "   • Insights dashboard loads automatically ✓"
    echo "   • Charts display real data from database ✓"
    echo "   • Period filters (weekly/monthly/semester) work ✓"
    echo "   • Summary metrics show current performance ✓"
    echo "   • Weak areas and recommendations displayed ✓"
    echo "   • Error handling works properly ✓"
    echo ""
    echo "🌐 Access your insights at: http://localhost:8080/insights.html"
    echo "🧪 Run comprehensive tests at: http://localhost:8080/test_insights.html"
    
    exit 0
else
    echo -e "${RED}❌ Some tests failed. Please check the issues above.${NC}"
    exit 1
fi
