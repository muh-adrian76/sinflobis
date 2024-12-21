/**
 * @type {import('node-pg-migrate').ColumnDefinitions | undefined}
 */

/**
 * @param pgm {import('node-pg-migrate').MigrationBuilder}
 * @param run {() => void | undefined}
 * @returns {Promise<void> | void}
 */
exports.up = (pgm) => {
    pgm.createTable('popular_times', {
      id: {
        type: 'INT',
        primaryKey: true,
      },
      hari: {
        type: 'VARCHAR(15)',
        notNull: true,
      },
      time: {
        type: 'TIME',
        notNull: true,
      },
      busy_percentage: {
        type: 'INT',
        notNull: true,
      },
      location_id: {
        type: 'INT',
        notNull: true,
        references: 'locations',
      },
    });
  };

/**
 * @param pgm {import('node-pg-migrate').MigrationBuilder}
 * @param run {() => void | undefined}
 * @returns {Promise<void> | void}
 */  
  exports.down = (pgm) => {
    pgm.dropTable('popular_times');
  };
  
