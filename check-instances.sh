#!/bin/bash
# check-instances.sh - Unified logging version
# Enhanced version with better error handling and debugging

# Exit on any error for debugging
set -e
trap 'log "Script terminated unexpectedly at line $LINENO"; exit 1' ERR

# Configuration
INSTANCES=6
RESTARTED=false
STARTER_DIR="/home/query"

# Unified logging function - only console output, no file writing
log() {
    local message="$1"
    local timestamp=$(date "+%Y-%m-%d %H:%M:%S")
    
    # Only output to console - let cron handle file redirection
    echo "[$timestamp] $message"
}

# Function to safely check screen sessions
check_screen_session() {
    local session_name="$1"
    if timeout 10 screen -list 2>/dev/null | grep -q "$session_name"; then
        return 0
    else
        return 1
    fi
}

# Function to safely check PHP processes
check_php_process() {
    local instance_id="$1"
    if timeout 10 ps aux | grep -v grep | grep -q "php app.php -i $instance_id"; then
        return 0
    else
        return 1
    fi
}

# Start of main script
log "=== INSTANCE CHECK STARTED - Checking $INSTANCES bot instances ==="
log "Script PID: $$, User: $(whoami), Directory: $(pwd)"

# Disable exit on error for the main checks
set +e

# Check if all screen sessions are running
log "=== PHASE 1: Checking Screen Sessions ==="
missing_instances=()

for i in $(seq 1 $INSTANCES); do
    if check_screen_session "SYSMO_$i"; then
        log "✓ Instance SYSMO_$i is running"
    else
        log "✗ Instance SYSMO_$i is missing"
        missing_instances+=("SYSMO_$i")
    fi
done

log "Screen session check completed. Missing: ${#missing_instances[@]} instances"

if [ ${#missing_instances[@]} -gt 0 ]; then
    log "⚠ Missing instances detected: ${missing_instances[*]}"
    log "→ Starting complete restart of all instances"
    RESTARTED=true
    
    # Change to starter directory
    if [ ! -d "$STARTER_DIR" ]; then
        log "✗ ERROR: Starter directory does not exist: $STARTER_DIR"
        exit 1
    fi
    
    cd "$STARTER_DIR" || {
        log "✗ ERROR: Failed to change to starter directory: $STARTER_DIR"
        exit 1
    }
    
    log "→ Changed to directory: $(pwd)"
    
    # Check if starter script exists
    if [ ! -f "./starter" ]; then
        log "✗ ERROR: Starter script not found: $STARTER_DIR/starter"
        exit 1
    fi
    
    # Stop all instances
    log "→ Executing: ./starter stop"
    if timeout 60 ./starter stop; then
        log "✓ All instances stopped successfully"
    else
        log "⚠ Failed to stop instances properly (timeout or error)"
    fi
    
    # Wait briefly
    log "→ Waiting 5 seconds before restart..."
    sleep 5
    
    # Start all instances
    log "→ Executing: ./starter start"
    if timeout 120 ./starter start; then
        log "✓ All instances started successfully"
    else
        log "✗ Failed to start instances properly (timeout or error)"
    fi
    
    log "=== RESTART COMPLETED - All instances restarted due to missing: ${missing_instances[*]} ==="

else
    log "✓ All screen sessions are running correctly"
    
    log "=== PHASE 2: CHECKING PHP PROCESSES ==="
    
    # Additional check of PHP processes
    problematic_instances=()
    for i in $(seq 1 $INSTANCES); do
        if check_php_process "$i"; then
            log "✓ PHP process for instance $i is running correctly"
        else
            log "⚠ PHP process for instance $i is missing (screen exists but no PHP process)"
            problematic_instances+=("$i")
            
            log "→ Attempting to restart individual instance $i"
            
            # Kill existing screen session
            if timeout 10 screen -X -S "SYSMO_$i" quit; then
                log "✓ Successfully quit screen session SYSMO_$i"
            else
                log "⚠ Failed to quit screen session SYSMO_$i (might not exist)"
            fi
            
            sleep 2
            
            # Change to starter directory
            cd "$STARTER_DIR" || {
                log "✗ Failed to change to starter directory for individual restart"
                continue
            }
            
            # Start new screen session
            if timeout 30 screen -dmS "SYSMO_$i" php app.php -i "$i"; then
                log "✓ Successfully restarted individual instance $i"
            else
                log "✗ Failed to restart individual instance $i"
            fi
            
            # Verify the restart worked
            sleep 3
            if check_php_process "$i"; then
                log "✓ Verified: Instance $i is now running properly"
            else
                log "✗ Verification failed: Instance $i still not running properly"
            fi
        fi
    done
    
    if [ ${#problematic_instances[@]} -eq 0 ]; then
        log "✓ All PHP processes are running correctly"
        log "=== CHECK COMPLETED - All $INSTANCES instances are healthy ==="
    else
        log "=== PARTIAL RESTART COMPLETED - Restarted individual instances: ${problematic_instances[*]} ==="
    fi
fi

# Final verification
log "=== FINAL VERIFICATION ==="

final_screen_count=0
final_php_count=0

for i in $(seq 1 $INSTANCES); do
    if check_screen_session "SYSMO_$i"; then
        final_screen_count=$((final_screen_count + 1))
    fi
    if check_php_process "$i"; then
        final_php_count=$((final_php_count + 1))
    fi
done

log "Final status: $final_screen_count/$INSTANCES screen sessions, $final_php_count/$INSTANCES PHP processes"

if [ "$final_screen_count" -eq "$INSTANCES" ] && [ "$final_php_count" -eq "$INSTANCES" ]; then
    log "✓ All instances are fully operational"
    exit_code=0
else
    log "✗ Some instances are still problematic"
    exit_code=1
fi

log "=== INSTANCE CHECK COMPLETED - Exit code: $exit_code ==="

exit $exit_code