import Table from "./Table";
import Buttons from "./Buttons";

export default function Body(props) {
    const {
        record,
        mode,
        apiBase,
        id,
        errors,
        submitting,
        setRecord,
        handleChange,
        handleBreakChange,
        handleSubmit,
    } = props;

    return (
        <>
            <Table
                record={record}
                mode={mode}
                errors={errors}
                handleChange={handleChange}
                handleBreakChange={handleBreakChange}
            />
            <Buttons
                mode={mode}
                record={record}
                apiBase={apiBase}
                id={id}
                submitting={submitting}
                handleSubmit={handleSubmit}
                setRecord={setRecord}
            />
        </>
    );
}
