#!/bin/bash

# LexiAid Phase 6 - Insights Demo Script
# Quick demonstration of the insights & analytics functionality

echo "🎓 LexiAid Phase 6 - Insights & Analytics Demo"
echo "=============================================="
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}📊 What's New in Phase 6:${NC}"
echo "• Comprehensive analytics dashboard"
echo "• Real-time performance tracking"
echo "• Interactive charts and visualizations"
echo "• Personalized study recommendations"
echo "• Progress tracking across multiple timeframes"
echo ""

echo -e "${YELLOW}🔍 Demo Data Overview:${NC}"
# Get current metrics
metrics=$(curl -s "http://localhost:8080/insights.php?user_id=5" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    summary = data['summary']
    weak_areas = data['weakAreas']
    
    print(f\"📈 Tasks: {summary['tasksCompleted']}/{summary['tasksAssigned']} completed\")
    print(f\"🎯 Quiz Average: {summary['quizAverage']}%\")
    print(f\"📚 Study Time: {summary['totalStudyTime']} hours this week\")
    print(f\"⚠️  Weak Areas: {len(weak_areas)} topics need attention\")
    
    if weak_areas:
        print(\"\\n🔸 Areas for improvement:\")
        for area in weak_areas[:3]:
            print(f\"   • {area['topic']}: {area['score']}%\")
            
except Exception as e:
    print(f\"Error: {e}\")
")

echo "$metrics"
echo ""

echo -e "${GREEN}🌐 Access the Insights Dashboard:${NC}"
echo "• Main Dashboard: http://localhost:8080/insights.html"
echo "• API Endpoint: http://localhost:8080/insights.php?user_id=5"
echo "• Test Suite: http://localhost:8080/test_insights.html"
echo ""

echo -e "${BLUE}📱 Features to Try:${NC}"
echo "1. Switch between Weekly, Monthly, and Semester views"
echo "2. Explore different chart types (bar, pie, line, radar)"
echo "3. Review personalized recommendations"
echo "4. Check your weak areas and focus topics"
echo "5. Monitor study consistency and productivity patterns"
echo ""

echo -e "${YELLOW}🎯 Sample Insights Available:${NC}"
echo "• Study time distribution by day of week"
echo "• Subject breakdown and time allocation"
echo "• Quiz performance trends over time"
echo "• Topic-specific performance radar"
echo "• Productivity patterns by time of day"
echo "• Resource utilization tracking"
echo ""

echo -e "${GREEN}✨ Try it now:${NC}"
echo "Open http://localhost:8080/insights.html in your browser!"
echo ""

# Check if browser can be opened automatically
if command -v xdg-open > /dev/null; then
    echo "🚀 Opening insights dashboard..."
    xdg-open "http://localhost:8080/insights.html" 2>/dev/null
elif command -v open > /dev/null; then
    echo "🚀 Opening insights dashboard..."
    open "http://localhost:8080/insights.html" 2>/dev/null
else
    echo "💡 Manually open http://localhost:8080/insights.html in your browser"
fi

echo ""
echo "🎉 Phase 6 is complete - Enjoy your new insights dashboard!"
