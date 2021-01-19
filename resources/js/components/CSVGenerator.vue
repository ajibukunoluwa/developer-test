<template>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">Table to CSV Generator</div>

                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th v-for="(column, index) in columns" :key="index">
                                    <input type="text"
                                           class="form-control"
                                           :value="column.title"
                                           @input="updateColumnKey(column, $event)"
                                    />
                                </th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody v-if="data.length">
                            <tr v-for="(row, index) in data" :key="index">
                                <td v-for="(dataColumn, columnName) in row" :key="columnName">
                                    <input type="text"
                                            class="form-control"
                                            :placeholder="placeHolders[columnName]"
                                            v-model="row[columnName]"
                                    />
                                    <div class="invalid-feedback force-display">{{ errors[`rows.${index}.${columnName}`] }}</div>

                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-danger"
                                            @click="removeRow(index)"> Delete
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="alert alert-secondary" role="alert" v-if="! data.length">
                            No records to show here. Kindly click on the add row button to begin!
                        </div>

                        <button type="button"
                                class="btn btn-secondary"
                                @click="addColumn()">Add Column
                        </button>

                        <button type="button"
                                class="btn btn-secondary"
                                @click="addRow()">Add Row
                        </button>
                    </div>

                    <div class="card-footer text-right">
                        <button class="btn btn-primary"
                                type="button"
                                @click="submit()"
                                :disabled="isExporting || !data.length"> {{ isExporting ? 'Exporting' : 'Export'}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        name: "CSVGenerator",

        data() {
            return {
                data: [
                    {
                        first_name: 'John',
                        last_name: 'Doe',
                        email_address: 'john.doe@example.com'
                    },
                    {
                        first_name: 'John',
                        last_name: 'Doe',
                        email_address: 'john.doe@example.com'
                    },

                ],
                columns: [
                    {
                        key: 'first_name',
                        title: 'First Name',
                    },
                    {
                        key: 'last_name',
                        title: 'Last Name',
                    },
                    {
                        key: 'email_address',
                        title: 'Email Address',
                    },

                ],
                errors: {},
                defaultInputFieldValue: '',
                defaultNewColumnTitle: 'New Column',
                duplicateRecordSuffix: 2,
                isExporting: false
            }
        },

        computed: {
            placeHolders() {
                let placeHolders = {};

                this.columns.forEach(
                    (column) => {
                        placeHolders[column.key] = column.title
                    }
                );

                return placeHolders;
            }
        },

        methods: {
            addRow() {
                let newRow = {};

                this.columns.forEach(
                    (column) => {
                        newRow[column.key] = this.defaultInputFieldValue;
                    }
                )

                this.data.push(newRow);
            },

            removeRow(rowIndex) {
                var proceedToDelete = confirm("Are you sure you want to delete this row?");

                if (proceedToDelete === true) {
                    this.data.splice(rowIndex, 1);

                    // Clear error messages for each
                    // column in this row
                    this.columns.forEach(
                        (column) => {
                            delete this.errors[`rows.${rowIndex}.${column['key']}`];
                        }
                    );
                }
            },

            addColumn() {
                let newColumnTitle = this.defaultNewColumnTitle;
                let newColumnKey = this.toSnakeCase(newColumnTitle);

                if (this.columnKeyExists(newColumnKey)) {
                    newColumnKey = `${newColumnKey}_${this.duplicateRecordSuffix}`
                    newColumnTitle = `${newColumnTitle} ${this.duplicateRecordSuffix}`
                    this.duplicateRecordSuffix++
                }

                let newColumn = {
                    key: newColumnKey,
                    title: newColumnTitle,
                };

                this.columns.push(newColumn);
                this.data.forEach(
                    (row) => {
                        row[newColumnKey] = this.defaultInputFieldValue;
                    }
                );
            },

            updateColumnKey(column, event) {
                let oldColumns = this.columns;
                let oldTitle = column.title;
                let oldKey = column.key;

                let newTitle = event.target.value;
                let newKey = this.toSnakeCase(newTitle);

                // Check if the column key exists
                // before making any change to the column
                let columnKeyExists = this.columnKeyExists(newKey);

                column.key = newKey;
                column.title = newTitle;

                if (columnKeyExists) {
                    newKey = newKey.substring(0, newKey.length - 1);
                    newTitle = newTitle.substring(0, newTitle.length - 1);

                    column.key = newKey;
                    column.title = newTitle;

                    if (! this.wasInsertedFromPaste(event)) {
                        // Only return when the event triggered is not a past event
                        // because paste events are usually triggered before the
                        // binded input value is updated
                        return;
                    }
                }

                this.updateColumnKeysInDataObject(oldKey, newKey);
            },

            updateColumnKeysInDataObject(oldKey, newKey) {
                // ========================================================
                // NB: The logic below was re-written to stop the input fields
                // from switching position when a column is renamed
                // ========================================================
                // STEPS
                // Loop through the present row,
                // replace the old key with the new key
                // keep previous keys and their value

                this.data.forEach(
                    (row, index) => {
                        let newRow = {}
                        for (var key in row) {
                            if (row.hasOwnProperty(key)) {
                                if (oldKey === key) {
                                    newRow[newKey] = row[key]
                                } else {
                                    newRow[key] = row[key]
                                }
                            }
                        }

                        this.data[index] = newRow
                    }
                );
            },

            columnKeyExists(newKey) {
                return !!this.columns.find(column => column.key === newKey);
            },

            toSnakeCase(word) {
                return word.toLowerCase().replace(/ /g,"_");
            },

            wasInsertedFromPaste(event) {
                return event.inputType === "insertFromPaste";
            },

            submit() {
                this.isExporting = true;

                return axios.patch('/api/csv-export', {
                    columns: this.columns,
                    rows: this.data
                }).then( response => {
                    this.downloadFile(response.data);
                }).catch(error => {
                    this.errors = {}
                    let errors = error.response.data.errors;
                    for (const [key, error] of Object.entries(errors)) {
                        this.errors[key] = error[0]
                    }

                }).finally(
                    () => this.isExporting = false
                );
            },

            downloadFile(data) {
                const anchor = document.createElement('a');
                anchor.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(data);
                anchor.target = '_blank';
                anchor.download = 'f3groups_csv_generated_file.csv';
                anchor.click();
            }
        }
    }
</script>

<style scoped>
    .force-display {
        display: block;
    }
</style>
