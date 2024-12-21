/**
 * @type {import('node-pg-migrate').ColumnDefinitions | undefined}
 */

/**
 * @param pgm {import('node-pg-migrate').MigrationBuilder}
 * @param run {() => void | undefined}
 * @returns {Promise<void> | void}
 */
exports.up = (pgm) => {
    pgm.createTable('locations', {
      id: {
        type: 'INT',
        primaryKey: true,
      },
      name: {
        type: 'TEXT',
        notNull: true,
      },
      latitude: {
        type: 'FLOAT(10,7)',
        notNull: true,
      },
      longitude: {
        type: 'FLOAT(10,7)',
        notNull: true,
      },
    });
  };

/**
 * @param pgm {import('node-pg-migrate').MigrationBuilder}
 * @param run {() => void | undefined}
 * @returns {Promise<void> | void}
 */  
  exports.down = (pgm) => {
    pgm.dropTable('locations');
  };
  
