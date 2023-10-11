    @Controller
    public class MiControlador {

    @RequestMapping('/inicio')
    public String mostrarPaginaInicio() {
    // Lógica para mostrar una página de inicio
    return "pagina-inicio";
    }

    @RequestMapping('/crear')
    public String mostrarFormularioCreacion() {
    // Lógica para mostrar un formulario de creación
    return "formulario-creacion";
    }

    @PostMapping('/guardar')
    public String guardarRecurso(@ModelAttribute Recurso recurso) {
    // Lógica para guardar un recurso
    return "redirect:/listar";
    }

    @RequestMapping('/ver/{id}')
    public String verRecurso(@PathVariable Long id, Model model) {
    // Lógica para mostrar un recurso específico
    return "detalle-recurso";
    }

    @RequestMapping('/editar/{id}')
    public String mostrarFormularioEdicion(@PathVariable Long id, Model model) {
    // Lógica para mostrar un formulario de edición
    return "formulario-edicion";
    }

    @PostMapping('/actualizar/{id}')
    public String actualizarRecurso(@PathVariable Long id, @ModelAttribute Recurso recurso) {
    // Lógica para actualizar un recurso
    return "redirect:/listar";
    }

    @RequestMapping('/eliminar/{id}')
    public String eliminarRecurso(@PathVariable Long id) {
    // Lógica para eliminar un recurso
    return "redirect:/listar";
    }
    }
