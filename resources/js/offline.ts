import Dexie, { type Table } from 'dexie';
import { v4 as uuidv4 } from 'uuid';

export interface QueuedTransaction {
    id?: number;
    uuid: string;
    table: 'transactions';
    payload: Record<string, any>;
    enqueuedAt: number;
    attempts: number;
}

class MonKouadjoDB extends Dexie {
    queue!: Table<QueuedTransaction, number>;

    constructor() {
        super('monkouadjo');
        this.version(1).stores({
            queue: '++id, uuid, table, enqueuedAt',
        });
    }
}

export const db = new MonKouadjoDB();

export async function enqueueOffline(table: 'transactions', payload: Record<string, any>): Promise<string> {
    const uuid = payload.uuid || uuidv4();
    await db.queue.add({
        uuid,
        table,
        payload: { ...payload, uuid },
        enqueuedAt: Date.now(),
        attempts: 0,
    });
    return uuid;
}

export async function pendingCount(): Promise<number> {
    return db.queue.count();
}

export async function flushQueue(csrfToken: string): Promise<{ sent: number; failed: number }> {
    let sent = 0;
    let failed = 0;
    const items = await db.queue.toArray();

    for (const item of items) {
        try {
            const url = item.table === 'transactions' ? '/transactions' : null;
            if (!url) continue;
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(item.payload),
            });
            if (res.ok || res.status === 302) {
                await db.queue.delete(item.id!);
                sent++;
            } else {
                await db.queue.update(item.id!, { attempts: item.attempts + 1 });
                failed++;
            }
        } catch {
            await db.queue.update(item.id!, { attempts: item.attempts + 1 });
            failed++;
        }
    }
    return { sent, failed };
}

export function installOnlineListener(csrfToken: string): void {
    const tryFlush = async () => {
        if (!navigator.onLine) return;
        const n = await pendingCount();
        if (n === 0) return;
        const { sent } = await flushQueue(csrfToken);
        if (sent > 0) {
            console.info(`[offline] ${sent} transaction(s) synchronisée(s)`);
        }
    };
    window.addEventListener('online', tryFlush);
    // Try once on load too
    setTimeout(tryFlush, 1500);
}
